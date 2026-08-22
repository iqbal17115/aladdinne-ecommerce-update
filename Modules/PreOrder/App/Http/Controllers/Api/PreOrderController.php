<?php

namespace Modules\PreOrder\App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Scopes\PreOderProduct;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Enums\PrePaymentStatus;
use Modules\PreOrder\App\Enums\RefundStatus;
use Modules\PreOrder\App\Http\Requests\CheckoutRequest;
use Modules\PreOrder\App\Http\Requests\PreOrderRequest;
use Modules\PreOrder\App\Models\PreOrder;
use Modules\PreOrder\App\Repositories\PreOrderRepository;
use Modules\PreOrder\App\Resources\PreOrderDetailsResource;
use Modules\PreOrder\App\Resources\PreOrderResource;

class PreOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customer = auth()->user()->customer;

        $perPage = (int) $request->input('per_page', 30);
        $status  = $request->input('order_status');

        $baseQuery = PreOrderRepository::query()
            ->where('customer_id', $customer->id);

        $ordersQuery = clone $baseQuery;

        if ($status && $status !== 'all') {
            $ordersQuery->where('order_status', $status);
        }

        $orders = $ordersQuery
            ->latest('id')
            ->paginate($perPage);

        $statusCounts = $baseQuery
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $statusWiseOrders = collect(PreOrderStatus::cases())
            ->mapWithKeys(fn($status) => [
                $status->value => $statusCounts[$status->value] ?? 0
            ]);

        return $this->json('orders', [
            'total' => $orders->total(),
            'status_wise_orders' => array_merge(
                ['all' => $statusWiseOrders->sum()],
                $statusWiseOrders->toArray()
            ),
            'preOrders' => PreOrderResource::collection($orders),
        ]);
    }

    public function show(PreOrder $preOrder): JsonResponse
    {
        abort_if($preOrder->customer_id !== auth()->user()->customer->id, 403);

        return $this->json('preOrder', [
            'preOrder' => PreOrderDetailsResource::make($preOrder)
        ]);
    }


    /**
     * Generate an online payment url for an existing pre-order whose payment
     * is still pending (e.g. placed as cash / online payment not completed),
     * so the customer can pay it later from the order details page.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'pre_order_id' => ['required'],
            'payment_method' => ['required'],
        ]);

        $preOrder = PreOrder::where('id', $request->pre_order_id)
            ->where('customer_id', auth()->user()->customer->id)
            ->first();

        if (! $preOrder) {
            return $this->json('Pre order not found', [], 404);
        }

        if (in_array($preOrder->order_status->value, [PreOrderStatus::CANCELLED->value, PreOrderStatus::REFUNDED->value], true)) {
            return $this->json('This order can not be paid', [], 422);
        }

        if ($preOrder->payment_status->value === PrePaymentStatus::PAID->value) {
            return $this->json('This order is already fully paid', [], 422);
        }

        if ($request->payment_method === PaymentMethod::CASH->value) {
            return $this->json('Please choose an online payment method', [], 422);
        }

        $isValidGateway = in_array(strtoupper((string) $request->payment_method), array_column(PaymentMethod::cases(), 'name'), true);
        if (! $isValidGateway) {
            return $this->json('Invalid payment method', [], 422);
        }

        if ($preOrder->payment_status->value === PrePaymentStatus::PREPAID->value) {
            // Order was already prepaid — charge the remaining balance as a new payment.
            $remaining = max(($preOrder->payable_amount - $preOrder->paid_amount), 0);
            if ($remaining <= 0) {
                return $this->json('No outstanding amount to pay', [], 422);
            }
            $payment = Payment::create([
                'amount' => $remaining,
                'payment_method' => $request->payment_method,
                'payment_from' => 'preOrder',
            ]);
            $payment->preOrders()->attach($preOrder->id);
        } else {
            // Pending — reuse the payment created at order time (keeps its amount,
            // e.g. the prepay amount for prepay products).
            $payment = $preOrder->payments()->first();
            if (! $payment) {
                $payment = Payment::create([
                    'amount' => max(($preOrder->payable_amount - $preOrder->paid_amount), 0),
                    'payment_method' => $request->payment_method,
                    'payment_from' => 'preOrder',
                ]);
                $payment->preOrders()->attach($preOrder->id);
            } else {
                $payment->update([
                    'payment_method' => $request->payment_method,
                    'is_paid' => false,
                ]);
            }
        }

        $paymentUrl = route('order.payment', ['payment' => $payment, 'gateway' => $request->payment_method]);

        return $this->json('Payment url generated', [
            'order_payment_url' => $paymentUrl,
        ]);
    }


    /**
     * Customer requests a refund for a delivered pre-order. The request is queued
     * for admin review; the admin approves and pays it from the dashboard.
     */
    public function refundRequest(Request $request)
    {
        $request->validate([
            'pre_order_id' => ['required'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $preOrder = PreOrder::where('id', $request->pre_order_id)
            ->where('customer_id', auth()->user()->customer->id)
            ->first();

        if (! $preOrder) {
            return $this->json('Pre order not found', [], 404);
        }

        if (! $preOrder->is_refundable) {
            return $this->json('This order is not refundable', [], 422);
        }

        if ($preOrder->order_status->value !== PreOrderStatus::DELIVERED->value) {
            return $this->json('Refund can only be requested for a delivered order', [], 422);
        }

        if ($preOrder->paidTotal() <= 0) {
            return $this->json('There is no paid amount to refund', [], 422);
        }

        if ($preOrder->hasActiveRefund()) {
            return $this->json('A refund is already in progress for this order', [], 422);
        }

        $preOrder->update([
            'refund_status' => RefundStatus::REQUESTED->value,
            'refund_reason' => $request->reason,
            'refund_note' => null,
        ]);

        return $this->json('Refund request submitted successfully', [
            'preOrder' => PreOrderDetailsResource::make($preOrder->fresh()),
        ]);
    }


    /**
     * Customer cancels their own pre-order. Only allowed while the order is still
     * in an early stage (before shipping). Any amount already paid is refunded
     * back to the customer automatically (same as a shop-side cancel).
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'pre_order_id' => ['required'],
        ]);

        $preOrder = PreOrder::where('id', $request->pre_order_id)
            ->where('customer_id', auth()->user()->customer->id)
            ->first();

        if (! $preOrder) {
            return $this->json('Pre order not found', [], 404);
        }

        $cancellableStatuses = [
            PreOrderStatus::REQUESTED->value,
            PreOrderStatus::ACCEPTED->value,
            PreOrderStatus::PREPAYMENT_REQUESTED->value,
            PreOrderStatus::PREPAYMENT_CONFIRMED->value,
        ];

        if (! in_array($preOrder->order_status->value, $cancellableStatuses, true)) {
            return $this->json('This order can no longer be cancelled', [], 422);
        }

        $preOrder->update(['order_status' => PreOrderStatus::CANCELLED->value]);

        // Refund whatever the customer already paid.
        if ($preOrder->paidTotal() > 0 && ! $preOrder->hasActiveRefund()) {
            PreOrderRepository::processRefund($preOrder->fresh(), false);

            return $this->json('Order cancelled and paid amount refunded', [
                'preOrder' => PreOrderDetailsResource::make($preOrder->fresh()),
            ]);
        }

        return $this->json('Order cancelled successfully', [
            'preOrder' => PreOrderDetailsResource::make($preOrder->fresh()),
        ]);
    }


    public function checkout(CheckoutRequest $request)
    {
        $product = ProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->find($request->product_id);
        if (!$product) {
            return $this->json('Sorry product not found', [], 404);
        }
        if (!$product->is_preorder) {
            return $this->json('Sorry product is not pre order', [], 422);
        }

        $checkoutData = PreOrderRepository::getTotalAmounts($request, $product);

        return $this->json('checkout success', [
            'checkout' => $checkoutData,
            'product' => ProductResource::make($product)
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(PreOrderRequest $request)
    {
        $product = ProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->find($request->product_id);
        if (!$product) {
            return $this->json('Sorry product not found', [], 404);
        }
        if (!$product->is_preorder) {
            return $this->json('Sorry product is not pre order', [], 422);
        }
        if (!$product->is_available) {
            return $this->json('Sorry product is not available now', [], 422);
        }
        if ($product->is_prepay && $request->payment_method == 'Cash Payment') {
            return $this->json('Sorry product is prepaid', [], 422);
        }
        // dd($request->all());
        $toUpper = strtoupper($request->payment_method);
        $paymentMethods = PaymentMethod::cases();

        $paymentMethod = $paymentMethods[array_search($toUpper, array_column(PaymentMethod::cases(), 'name'))];

        $payment = PreOrderRepository::storeByRequestFrom($request, $product,$paymentMethod);

        $paymentUrl = null;
        if ($paymentMethod->name != 'CASH') {
            $paymentUrl = route('order.payment', ['payment' => $payment, 'gateway' => $request->payment_method]);
        }

        return $this->json('Order created successfully', [
            'order_payment_url' => $paymentUrl,
        ]);
    }
}
