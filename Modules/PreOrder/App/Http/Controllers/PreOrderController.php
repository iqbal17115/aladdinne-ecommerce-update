<?php

namespace Modules\PreOrder\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CourierTracking;
use App\Models\Driver;
use App\Repositories\DriverRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use App\Services\SteadFastService;
use Carbon\Carbon;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Enums\PrePaymentStatus;
use Modules\PreOrder\App\Enums\RefundStatus;
use Modules\PreOrder\App\Models\PreOrder;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Repositories\PreOrderRepository;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Mpdf\Mpdf;

class PreOrderController extends Controller
{
    public function index()
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('preOrderForShop', $setting);
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to'] = request('to') ?? now()->format('m/d/Y');
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $status = request('status');
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $data['isRoot'] =$shop->id === $rootShop->id ? true :false;

        $data['preOrders'] = PreOrderRepository::query()
            ->with('shop', 'customer.user')
            ->whereBetween('created_at', [$from, $to])
            ->when(!$data['isRoot'], function ($q) use ($shop) {
                return $q->where('shop_id', $shop?->id);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('order_status', $status);
            })
            ->when($search, fn($q) => $q->whereLike('order_code', '%' . $search . '%'))
            ->orderBy("created_at", "desc")
            ->paginate(10)
            ->withQueryString();
        return view('preorder::preOrder.index', $data);
    }
    public function show(PreOrder $preOrder)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        if ($shop->id !== $rootShop->id && $preOrder->shop_id !== $shop->id) {
            abort(404);
        }
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('preOrderForShop', $setting);
        $data['preOrder'] = $preOrder;
        $data['orderStatus'] = PreOrderRepository::allowedNextStatuses($data['preOrder']->order_status);
        $data['riders'] = Driver::whereHas('user', function ($query) {
            return $query->where('is_active', true);
        })->get();
        $data['suppliers'] = collect();
        if (module_exists('Purchase')) {
            $data['suppliers'] = \Modules\Purchase\App\Models\Supplier::where('shop_id', $shop->id)->latest('id')->get();
        }
        $data['courierTracking'] = CourierTracking::where('pre_order_id', $preOrder->id)->latest('id')->first();
        return view('preorder::preOrder.show', $data);
    }

    /**
     * Send pre-order to SteadFast courier.
     */
    public function assignCourier(PreOrder $preOrder)
    {
        if (! config('steadfast.active')) {
            return back()->with('error', __('SteadFast courier is not active.'));
        }

        if (in_array($preOrder->order_status, [
            PreOrderStatus::CANCELLED,
            PreOrderStatus::DELIVERED,
            PreOrderStatus::REFUNDED,
        ])) {
            return back()->with('error', __('Courier cannot be assigned.'));
        }

        if (CourierTracking::where('pre_order_id', $preOrder->id)->exists()) {
            return back()->with('error', __('Order has already been sent to courier.'));
        }

        $response = (new SteadFastService)->createCourierOrderForPreOrder($preOrder);

        if (! $response['success']) {
            return back()->with('error', $response['message']);
        }

        return back()->with('success', __('Order sent to SteadFast courier successfully.'));
    }

    public function statusChange(PreOrder $preOrder, Request $request)
    {
        $request->validate(['status' => 'required']);
        $rootShop = generaleSetting('rootShop');
        $allowedStatuses = PreOrderRepository::allowedNextStatuses($preOrder->order_status);
        $status = PreOrderStatus::tryFrom($request->status);
        if (!in_array($status, $allowedStatuses)) {
            return back()->with('error', __('Cannot change to this status.'));
        }
        $preOrder->update(['order_status' => $request->status]);

        if ($request->status == PreOrderStatus::DELIVERED->value && $preOrder->shop_id != $rootShop->id) {
            PreOrderRepository::updateWalletAndTransaction($preOrder);
        }

        if ($request->status == PreOrderStatus::DELIVERED->value) {
            PreOrderRepository::decreaseStockOnDelivery($preOrder);
        }

        // Admin cancelled before delivery — auto-refund whatever the customer paid.
        if ($request->status == PreOrderStatus::CANCELLED->value
            && $preOrder->paidTotal() > 0
            && ! $preOrder->hasActiveRefund()) {
            PreOrderRepository::processRefund($preOrder->fresh(), false);
            return back()->with('success', __('Order cancelled and paid amount refunded to the customer.'));
        }

        // Admin moved a delivered order straight to Refunded from the dropdown —
        // record the refund and reverse the shop wallet just like the request flow.
        if ($request->status == PreOrderStatus::REFUNDED->value
            && ! $preOrder->hasActiveRefund()) {
            PreOrderRepository::processRefund($preOrder->fresh(), true);
            return back()->with('success', __('Order refunded to the customer.'));
        }

        return back();
    }

    /**
     * Approve a customer's refund request (delivered order). The money is paid out
     * in a second step via refundPay.
     */
    public function refundApprove(PreOrder $preOrder)
    {
        if ($preOrder->refund_status?->value !== RefundStatus::REQUESTED->value) {
            return back()->with('error', __('There is no pending refund request to approve.'));
        }
        $preOrder->update(['refund_status' => RefundStatus::APPROVED->value]);
        return back()->with('success', __('Refund request approved.'));
    }

    /**
     * Reject a customer's refund request.
     */
    public function refundReject(PreOrder $preOrder, Request $request)
    {
        if ($preOrder->refund_status?->value !== RefundStatus::REQUESTED->value) {
            return back()->with('error', __('There is no pending refund request to reject.'));
        }
        $preOrder->update([
            'refund_status' => RefundStatus::REJECTED->value,
            'refund_note' => $request->refund_note,
        ]);
        return back()->with('success', __('Refund request rejected.'));
    }

    /**
     * Pay out an approved refund: reverse the shop wallet and mark the order refunded.
     */
    public function refundPay(PreOrder $preOrder)
    {
        if ($preOrder->refund_status?->value !== RefundStatus::APPROVED->value) {
            return back()->with('error', __('Refund must be approved before it can be paid.'));
        }
        PreOrderRepository::processRefund($preOrder, true);
        return back()->with('success', __('Refund paid to the customer successfully.'));
    }

    /**
     * Store the pre-ordered product as a purchase record in the Purchase module.
     * Reuses the Purchase module's own repository (no edits to that module) and
     * only works when the Purchase module is installed and enabled. Quantity is
     * taken from the pre-order item; supplier and buying price come from the admin.
     */
    public function storePurchase(PreOrder $preOrder, Request $request)
    {
        if (! module_exists('Purchase')) {
            return back()->with('error', __('Purchase module is not available.'));
        }

        if ($preOrder->purchase_id) {
            return back()->with('error', __('A purchase has already been created for this pre-order.'));
        }

        if (in_array($preOrder->order_status, [PreOrderStatus::CANCELLED, PreOrderStatus::DELIVERED, PreOrderStatus::REFUNDED])) {
            return back()->with('error', __('A purchase cannot be created for a cancelled, delivered or refunded pre-order.'));
        }

        $request->validate([
            'supplier_id' => ['required'],
        ]);

        $item = $preOrder->preOrderItem;
        $product = $item ? \App\Models\Product::withoutGlobalScopes()->find($item->product_id) : null;
        if (! $product) {
            return back()->with('error', __('Product not found for this pre-order.'));
        }

        // Buying price comes from the product itself (falls back to the price
        // captured on the pre-order item) — the admin no longer enters it.
        $buyingPrice = $product->buy_price ?? $item->buying_price ?? 0;

        $note = __('Pre-order :code', ['code' => $preOrder->order_code]);

        $purchase = \Modules\Purchase\App\Repositories\PurchaseRepository::storeByRequest(new Request([
            'name' => $preOrder->order_code,
            'supplier_id' => $request->supplier_id,
            'receive_date' => now()->format('Y-m-d'),
            'note' => $note,
        ]));

        \Modules\Purchase\App\Repositories\PurchaseRepository::attachBarcode($product, $purchase, null, new Request([
            'product_id' => $product->id,
            'is_sku' => 'false',
            'quantity' => $item->quantity,
            'buying_price' => $buyingPrice,
            'note' => $note,
        ]));

        $preOrder->update(['purchase_id' => $purchase->id]);

        return to_route('shop.purchase.show', $purchase->id)->withSuccess(__('Purchase created from pre-order successfully.'));
    }

    public function paymentStatusToggle(PreOrder $preOrder)
    {
        if ($preOrder->payment_status->value == PrePaymentStatus::PAID->value || $preOrder->order_status->value == PreOrderStatus::CANCELLED->value) {
            return back()->with('error', __('Payment status cannot be changed.'));
        }
        $preOrder->update([
            'payment_status' => PrePaymentStatus::PAID->value,
            'paid_amount' => $preOrder->payable_amount
        ]);
        return back()->with('success', __('Payment status updated successfully'));
    }

    public function assignOrder(PreOrder $preOrder, Request $request)
    {

        if (in_array($preOrder->order_status, [
            PreOrderStatus::CANCELLED,
            PreOrderStatus::DELIVERED,
            PreOrderStatus::REFUNDED,
        ])) {
            return back()->with('error', __('Rider cannot be assigned.'));
        }


        $driver = Driver::find($request->rider);

        if (! $driver) {
            return back()->withError(__('Rider not found, please try again'));
        }

        DriverRepository::assignPreOrder($preOrder, $driver);

        return back()->withSuccess(__('Rider assigned successfully'));
    }

    public function orderInvoice(PreOrder $preOrder)
    {
        $preOrderCode = '#' . $preOrder->order_code;
        $qrCode = new EndroidQrCode($preOrderCode);
        $qrCode->setSize(100);
        $writer = new PngWriter;
        $qrCodeImage = $writer->write($qrCode)->getDataUri();
        // pdf config
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $fontData['kalpurush'] = [
            'R' => 'kalpurush.ttf',
        ];
        $paperSize = 'A4';
        $mPdf = new Mpdf([
            'mode' => 'UTF-8',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/public/mpdf_tmp'),
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData,
            'format' => $paperSize,
        ]);
        $view = view('preorder::PDF.invoice', compact('preOrder', 'qrCodeImage'))->render();
        $mPdf->WriteHTML($view);
        // Output the PDF as a download
        // return $mPdf->Output('invoice-' . $preOrder->order_code . '.pdf', 'D');
        // Output the PDF as a stream
        return $mPdf->Output('invoice-' . $preOrder->order_code . '.pdf', 'I');
    }

    public function orderPaymentInvoice(PreOrder $preOrder)
    {
        // pdf config
        $defaultConfig = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $fontData['kalpurush'] = [
            'R' => 'kalpurush.ttf',
        ];

        $paperSize = 'A4';

        $mPdf = new Mpdf([
            'mode' => 'UTF-8',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/public/mpdf_tmp'),
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData,
            'format' => $paperSize,
        ]);

        $view = view('preorder::PDF.payment-slip', compact('preOrder'))->render();
        $mPdf->WriteHTML($view);

        $pdfContent = $mPdf->Output('payment-slip-' . $preOrder->order_code . '.pdf', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="payment-slip-' . $preOrder->order_code . '.pdf"',
        ]);
    }
}
