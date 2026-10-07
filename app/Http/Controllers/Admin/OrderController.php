<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Driver;
use App\Models\GeneraleSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\Thana;
use App\Models\User;
use App\Models\CourierTracking;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Services\AdminOrderItemService;
use App\Services\NotificationServices;
use App\Services\SteadFastService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a order list with filter status.
     */
    public function index($status = null)
    {
        $status = $status ? str_replace('_', ' ', $status) : '';

        $generaleSetting = GeneraleSetting::first();
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = User::role(Roles::ROOT->value)->first()?->shop;
        }

        $orders = OrderRepository::query()
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('order_status', $status);
            })->latest('id')->paginate(20);

        return view('admin.order.index', compact('orders', 'status'));
    }

    /**
     * Display the order details.
     */
    public function show(Order $order)
    {
        $orderStatus = OrderStatus::cases();
        $order->load(['products.sizes', 'products.colors', 'payments']);
        $areas = Area::isActive()->orderBy('name')->get();
        $address = $order->address;
        $matchingArea = $areas->firstWhere('name', $order->order_area);
        $selectedAreaId = old('area_id', $matchingArea?->id ?? $address?->area_id);
        $thanas = Thana::isActive()
            ->when($selectedAreaId, fn ($query) => $query->where('area_id', $selectedAreaId))
            ->orderBy('name')
            ->get();
        $matchingThana = $thanas->firstWhere('name', $order->order_thana);
        $selectedThanaId = old('thana_id', $matchingThana?->id ?? $address?->thana_id);
        $canEditItems = $order->order_status === OrderStatus::PENDING
            && $order->payment_status === PaymentStatus::PENDING
            && ! $order->payments->contains(fn ($payment) => $payment->is_paid)
            && ! $order->products->contains(fn ($product) => $product->is_digital);
        $availableProductsData = collect();

        if ($canEditItems) {
            $availableProductsData = $order->shop->products()
                ->isActive()
                ->where('quantity', '>', 0)
                ->where('is_digital', false)
                ->with(['sizes', 'colors', 'unit', 'flashSales'])
                ->orderBy('name')
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => (int) $product->quantity,
                    'base_price' => (float) ($product->discount_price > 0 ? $product->discount_price : $product->price),
                    'sale_price' => $product->flashSales->first()?->pivot?->price,
                    'sale_remaining' => max(0, (int) ($product->flashSales->first()?->pivot?->quantity ?? 0)
                        - (int) ($product->flashSales->first()?->pivot?->sale_quantity ?? 0)),
                    'unit' => $product->unit?->name,
                    'sizes' => $product->sizes->map(fn ($size) => [
                        'id' => $size->id,
                        'name' => $size->name,
                        'price' => (float) ($size->pivot->price ?? 0),
                    ])->values(),
                    'colors' => $product->colors->map(fn ($color) => [
                        'id' => $color->id,
                        'name' => $color->name,
                        'price' => (float) ($color->pivot->price ?? 0),
                    ])->values(),
                ])->values();
        }

        $riders = Driver::whereHas('user', function ($query) {
            return $query->where('is_active', true);
        })->get();

        $courierTracking = CourierTracking::where('order_id', $order->id)->latest('id')->first();

        return view('admin.order.show', compact('order', 'orderStatus', 'riders', 'courierTracking', 'canEditItems', 'availableProductsData', 'areas', 'thanas', 'selectedAreaId', 'selectedThanaId'));
    }

    public function updateItems(Order $order, Request $request, AdminOrderItemService $itemService)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.remove' => ['nullable', 'boolean'],
            'items.*.size_id' => ['nullable', 'integer'],
            'items.*.color_id' => ['nullable', 'integer'],
            'items.*.price' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'new_item' => ['nullable', 'array'],
            'new_item.product_id' => ['nullable', 'integer'],
            'new_item.quantity' => ['nullable', 'integer', 'min:1', 'required_with:new_item.product_id'],
            'new_item.size_id' => ['nullable', 'integer'],
            'new_item.color_id' => ['nullable', 'integer'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'delivery_charge_manual' => ['nullable', 'boolean'],
        ]);

        $itemService->update(
            $order,
            $validated['items'],
            $validated['new_item'] ?? [],
            (float) ($validated['delivery_charge'] ?? null),
            (bool) ($validated['delivery_charge_manual'] ?? false),
        );

        return redirect()->route('admin.order.show', $order)
            ->with('success', __('Order items updated successfully.'));
    }

    /**
     * Update order-specific delivery details and the internal admin note.
     */
    public function updateDeliveryDetails(Order $order, Request $request)
    {
        $validated = $request->validate([
            'order_phone' => ['nullable', 'string', 'max:50'],
            'order_address_line' => ['nullable', 'string', 'max:2000'],
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'thana_id' => ['nullable', 'integer', 'exists:thanas,id'],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $area = ! empty($validated['area_id'])
            ? Area::isActive()->find($validated['area_id'])
            : null;
        if (! empty($validated['area_id']) && ! $area) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'area_id' => __('Choose an active area.'),
            ]);
        }

        $thana = null;
        if (! empty($validated['thana_id'])) {
            $thana = Thana::isActive()
                ->where('area_id', $validated['area_id'] ?? null)
                ->find($validated['thana_id']);
            if (! $thana) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'thana_id' => __('Choose a thana within the selected area.'),
                ]);
            }
        }

        unset($validated['area_id'], $validated['thana_id']);
        $validated['order_area'] = $area?->name;
        $validated['order_thana'] = $thana?->name;

        $order->update($validated);

        return redirect()->route('admin.order.show', $order)
            ->with('success', __('Order delivery details updated successfully.'));
    }

    /**
     * Send order to SteadFast courier.
     */
    public function assignCourier(Order $order)
    {
        if (! config('steadfast.active')) {
            return back()->with('error', __('SteadFast courier is not active.'));
        }

        if (in_array($order->order_status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED])) {
            return back()->with('error', __('Courier cannot be assigned.'));
        }

        if (CourierTracking::where('order_id', $order->id)->exists()) {
            return back()->with('error', __('Order has already been sent to courier.'));
        }

        $response = (new SteadFastService)->createCourierOrder($order);

        if (! $response['success']) {
            return back()->with('error', $response['message']);
        }

        return back()->with('success', __('Order sent to SteadFast courier successfully.'));
    }

    /**
     * Update the order status.
     */
    public function statusChange(Order $order, Request $request)
    {
        $request->validate(['status' => 'required']);

        $order->update(['order_status' => $request->status]);

        $title = 'Order status updated';
        $message = 'Your order status updated to '.$request->status;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        if ($request->status == OrderStatus::CANCELLED->value) {
            foreach ($order->products as $product) {

                $qty = $product->pivot->quantity;

                $product->update(['quantity' => $product->quantity + $qty]);

                $flashSale = $product->flashSales?->first();
                $flashSaleProduct = null;

                if ($flashSale) {
                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                    if ($flashSaleProduct && $product->pivot?->price) {
                        if ($flashSaleProduct->pivot->sale_quantity >= $qty && ($product->pivot?->price == $flashSaleProduct->pivot->price)) {
                            $flashSale->products()->updateExistingPivot($product->id, [
                                'sale_quantity' => $flashSaleProduct->pivot->sale_quantity - $qty,
                            ]);
                        }
                    }
                }
            }

            if (function_exists('module_exists') && module_exists('Purchase')) {
                $order->productStockOuts()->delete();
            }
        }

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Order status updated successfully.'));
    }

    /**
     * Update the payment status.
     */
    public function paymentStatusToggle(Order $order)
    {
        if ($order->payment_status->value == PaymentStatus::PAID->value) {
            return back()->with('error', __('When order is paid, payment status cannot be changed.'));
        }
        $order->update(['payment_status' => PaymentStatus::PAID->value]);

        $title = 'Payment status updated';
        $message = __('Your payment status updated to paid. order code: ').$order->prefix.$order->order_code;
        $deviceKeys = $order->customer->user->devices->pluck('key')->toArray();

        try {
            NotificationServices::sendNotification($message, $deviceKeys, $title);
        } catch (\Throwable $th) {
        }

        $notify = (object) [
            'title' => $title,
            'content' => $message,
            'user_id' => $order->customer->user_id,
            'type' => 'order',
        ];

        NotificationRepository::storeByRequest($notify);

        return back()->with('success', __('Payment status updated successfully'));
    }
}
