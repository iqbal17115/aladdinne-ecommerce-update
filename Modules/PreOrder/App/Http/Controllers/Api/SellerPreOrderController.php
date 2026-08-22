<?php

namespace Modules\PreOrder\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Repositories\DriverRepository;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Models\PreOrder;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Repositories\PreOrderRepository;
use Modules\PreOrder\App\Resources\SellerPreOrderDetailsResource;
use Modules\PreOrder\App\Resources\SellerPreOrderResource;

class SellerPreOrderController extends Controller
{
    /**
     * List the shop's pre-orders. The root shop sees every pre-order; a
     * sub-shop sees only its own.
     */
    public function index(Request $request)
    {
        $this->authorizePreOrder();

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $isRoot = $shop->id === $rootShop->id;

        $perPage = (int) ($request->per_page ?? 15);
        $search = $request->search;
        $status = $request->order_status;

        $baseQuery = PreOrderRepository::query()
            ->with('shop', 'customer.user', 'preOrderItem')
            ->when(! $isRoot, fn ($q) => $q->where('shop_id', $shop?->id));

        $ordersQuery = (clone $baseQuery)
            ->when($status && $status !== 'all', fn ($q) => $q->where('order_status', $status))
            ->when($search, fn ($q) => $q->where('order_code', 'like', "%$search%"));

        $orders = $ordersQuery->latest('id')->paginate($perPage);

        $statusCounts = (clone $baseQuery)
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $statusWiseOrders = collect(PreOrderStatus::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $statusCounts[$case->value] ?? 0]);

        return $this->json('Pre-order list', [
            'total' => $orders->total(),
            'status_wise_orders' => array_merge(
                ['all' => $statusWiseOrders->sum()],
                $statusWiseOrders->toArray()
            ),
            'preOrders' => SellerPreOrderResource::collection($orders),
        ]);
    }

    /**
     * Show a single pre-order's details.
     */
    public function show(Request $request)
    {
        $this->authorizePreOrder();

        $preOrder = $this->findOwnedOrder($request->order_id);

        return $this->json('Pre-order details', [
            'preOrder' => SellerPreOrderDetailsResource::make($preOrder),
        ]);
    }

    /**
     * Change a pre-order's status (mirrors the dashboard status change,
     * including wallet, stock and auto-refund side effects).
     */
    public function update(Request $request)
    {
        $this->authorizePreOrder();

        $request->validate([
            'order_id' => ['required'],
            'status' => ['required'],
        ]);

        $preOrder = $this->findOwnedOrder($request->order_id);
        $rootShop = generaleSetting('rootShop');

        $allowedStatuses = PreOrderRepository::allowedNextStatuses($preOrder->order_status);
        $status = PreOrderStatus::tryFrom($request->status);
        if (! in_array($status, $allowedStatuses, true)) {
            return $this->json(__('Cannot change to this status.'), [], 422);
        }

        $preOrder->update(['order_status' => $request->status]);

        if ($request->status == PreOrderStatus::DELIVERED->value && $preOrder->shop_id != $rootShop->id) {
            PreOrderRepository::updateWalletAndTransaction($preOrder);
        }

        if ($request->status == PreOrderStatus::DELIVERED->value) {
            PreOrderRepository::decreaseStockOnDelivery($preOrder);
        }

        // Shop cancelled before delivery — auto-refund whatever the customer paid.
        if ($request->status == PreOrderStatus::CANCELLED->value
            && $preOrder->paidTotal() > 0
            && ! $preOrder->hasActiveRefund()) {
            PreOrderRepository::processRefund($preOrder->fresh(), false);

            return $this->json(__('Order cancelled and paid amount refunded to the customer.'), [
                'preOrder' => SellerPreOrderDetailsResource::make($preOrder->fresh()),
            ]);
        }

        // Delivered order moved straight to Refunded.
        if ($request->status == PreOrderStatus::REFUNDED->value
            && ! $preOrder->hasActiveRefund()) {
            PreOrderRepository::processRefund($preOrder->fresh(), true);

            return $this->json(__('Order refunded to the customer.'), [
                'preOrder' => SellerPreOrderDetailsResource::make($preOrder->fresh()),
            ]);
        }

        return $this->json(__('Status updated successfully'), [
            'preOrder' => SellerPreOrderDetailsResource::make($preOrder->fresh()),
        ]);
    }

    /**
     * List active riders available for assignment.
     */
    public function riders()
    {
        $this->authorizePreOrder();

        $riders = Driver::whereHas('user', fn ($q) => $q->where('is_active', true))->get();

        $data = $riders->map(fn ($rider) => [
            'id' => $rider->id,
            'name' => $rider->user?->fullName,
            'phone' => $rider->user?->phone,
            'thumbnail' => $rider->user?->thumbnail,
            'incomplete_orders' => (int) $rider->incompleteOrders(),
        ]);

        return $this->json('Rider list', [
            'riders' => $data,
        ]);
    }

    /**
     * Assign a rider to a pre-order for delivery.
     */
    public function assignRider(Request $request)
    {
        $this->authorizePreOrder();

        $request->validate([
            'order_id' => ['required'],
            'rider_id' => ['required'],
        ]);

        $preOrder = $this->findOwnedOrder($request->order_id);

        if (in_array($preOrder->order_status, [
            PreOrderStatus::CANCELLED,
            PreOrderStatus::DELIVERED,
            PreOrderStatus::REFUNDED,
        ], true)) {
            return $this->json(__('Rider cannot be assigned.'), [], 422);
        }

        $driver = Driver::find($request->rider_id);
        if (! $driver) {
            return $this->json(__('Rider not found, please try again'), [], 422);
        }

        DriverRepository::assignPreOrder($preOrder, $driver);

        return $this->json(__('Rider assigned successfully'), [
            'preOrder' => SellerPreOrderDetailsResource::make($preOrder->fresh()),
        ]);
    }

    /**
     * Commission list for delivered pre-orders. The root shop sees the
     * commission earned on sub-shop orders; a sub-shop sees its own.
     */
    public function commissionList(Request $request)
    {
        $this->authorizePreOrder();

        [$from, $to] = $this->dateRange($request);
        $shopId = generaleSetting('shop')?->id;
        $rootShop = generaleSetting('rootShop');
        $isRoot = $shopId === $rootShop->id;
        $search = $request->search;
        $perPage = (int) ($request->per_page ?? 30);

        $baseQuery = PreOrderRepository::query()
            ->delivered()
            ->when($isRoot, fn ($q) => $q->where('pre_orders.shop_id', '!=', $shopId))
            ->when(! $isRoot, fn ($q) => $q->where('pre_orders.shop_id', $shopId))
            ->join('pre_order_items', 'pre_orders.id', '=', 'pre_order_items.pre_order_id')
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->when($search, fn ($q) => $q->filter(['search' => $search]));

        $list = (clone $baseQuery)
            ->groupBy('pre_orders.id', 'pre_orders.admin_commission', 'pre_orders.created_at', 'pre_orders.order_code')
            ->selectRaw('
                pre_orders.id,
                pre_orders.admin_commission,
                pre_orders.created_at,
                pre_orders.order_code,
                SUM(pre_order_items.quantity) AS total_product,
                (SUM(pre_order_items.quantity * pre_order_items.price) - pre_orders.admin_commission) AS sale_earning
            ')
            ->orderByDesc('sale_earning')
            ->paginate($perPage);

        $totals = (clone $baseQuery)
            ->selectRaw('
                SUM(pre_orders.admin_commission) AS total_commission,
                SUM(pre_order_items.quantity * pre_order_items.price) AS total_amount,
                (SUM(pre_order_items.quantity * pre_order_items.price) - SUM(pre_orders.admin_commission)) AS total_earning
            ')->first();

        return $this->json('Commission list', [
            'total' => $list->total(),
            'summary' => [
                'total_commission' => round((float) ($totals->total_commission ?? 0), 2),
                'total_amount' => round((float) ($totals->total_amount ?? 0), 2),
                'total_earning' => round((float) ($totals->total_earning ?? 0), 2),
            ],
            'commissions' => collect($list->items())->map(fn ($item) => [
                'id' => $item->id,
                'order_code' => $item->order_code,
                'created_at' => Carbon::parse($item->created_at)->format('d M, Y'),
                'total_product' => (int) $item->total_product,
                'admin_commission' => round((float) $item->admin_commission, 2),
                'sale_earning' => round((float) $item->sale_earning, 2),
            ]),
        ]);
    }

    /**
     * Per-shop profit report for delivered pre-orders (own shop only).
     */
    public function profitReport(Request $request)
    {
        $this->authorizePreOrder();

        [$from, $to] = $this->dateRange($request);
        $shopId = generaleSetting('shop')?->id;
        $search = $request->search;
        $perPage = (int) ($request->per_page ?? 30);

        $baseQuery = PreOrderRepository::query()
            ->delivered()
            ->where('pre_orders.shop_id', $shopId)
            ->join('pre_order_items', 'pre_orders.id', '=', 'pre_order_items.pre_order_id')
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->when($search, fn ($q) => $q->filter(['search' => $search]));

        $list = (clone $baseQuery)
            ->groupBy('pre_orders.id', 'pre_orders.created_at', 'pre_orders.order_code')
            ->selectRaw('
                pre_orders.id,
                pre_orders.created_at,
                pre_orders.order_code,
                SUM(pre_order_items.quantity) AS total_product,
                SUM(pre_order_items.quantity * pre_order_items.price) AS sale_amount,
                SUM(pre_order_items.quantity * pre_order_items.buying_price) AS buying_amount,
                SUM(pre_order_items.quantity * (pre_order_items.price - pre_order_items.buying_price)) AS profit
            ')
            ->orderByDesc('pre_orders.created_at')
            ->paginate($perPage);

        $totals = (clone $baseQuery)
            ->selectRaw('
                SUM(pre_order_items.quantity * pre_order_items.price) AS total_sale,
                SUM(pre_order_items.quantity * pre_order_items.buying_price) AS total_buying,
                SUM(pre_order_items.quantity * (pre_order_items.price - pre_order_items.buying_price)) AS total_profit
            ')->first();

        return $this->json('Profit report', [
            'total' => $list->total(),
            'summary' => [
                'total_sale' => round((float) ($totals->total_sale ?? 0), 2),
                'total_buying' => round((float) ($totals->total_buying ?? 0), 2),
                'total_profit' => round((float) ($totals->total_profit ?? 0), 2),
            ],
            'reports' => collect($list->items())->map(fn ($item) => [
                'id' => $item->id,
                'order_code' => $item->order_code,
                'created_at' => Carbon::parse($item->created_at)->format('d M, Y'),
                'total_product' => (int) $item->total_product,
                'sale_amount' => round((float) $item->sale_amount, 2),
                'buying_amount' => round((float) $item->buying_amount, 2),
                'profit' => round((float) $item->profit, 2),
            ]),
        ]);
    }

    /**
     * Create a purchase record (in the Purchase module) from a pre-order — mirrors
     * the dashboard "Purchase" action. Quantity comes from the pre-order item;
     * buying price from the product; supplier is chosen by the shop. Requires the
     * Purchase module to be installed and enabled.
     */
    public function storePurchase(Request $request)
    {
        $this->authorizePreOrder();

        $request->validate([
            'order_id' => ['required'],
            'supplier_id' => ['required'],
        ]);

        if (! module_exists('Purchase')) {
            return $this->json(__('Purchase module is not available.'), [], 422);
        }

        $preOrder = $this->findOwnedOrder($request->order_id);

        if ($preOrder->purchase_id) {
            return $this->json(__('A purchase has already been created for this pre-order.'), [], 422);
        }

        if (in_array($preOrder->order_status, [PreOrderStatus::CANCELLED, PreOrderStatus::DELIVERED, PreOrderStatus::REFUNDED], true)) {
            return $this->json(__('A purchase cannot be created for a cancelled, delivered or refunded pre-order.'), [], 422);
        }

        $item = $preOrder->preOrderItem;
        $product = $item ? \App\Models\Product::withoutGlobalScopes()->find($item->product_id) : null;
        if (! $product) {
            return $this->json(__('Product not found for this pre-order.'), [], 422);
        }

        // Buying price comes from the product itself (falls back to the price
        // captured on the pre-order item) — the shop no longer enters it.
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

        return $this->json(__('Purchase created from pre-order successfully.'), [
            'purchase' => [
                'id' => $purchase->id,
                'purchase_code' => $purchase->purchase_code,
            ],
            'preOrder' => SellerPreOrderDetailsResource::make($preOrder->fresh()),
        ]);
    }

    /**
     * Resolve a pre-order the current shop is allowed to manage.
     */
    private function findOwnedOrder($id): PreOrder
    {
        $preOrder = $id ? PreOrderRepository::query()->find($id) : null;

        if (! $preOrder) {
            throw new HttpResponseException(
                $this->json(__('Pre-order not found'), [], 404)
            );
        }

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        if ($shop->id !== $rootShop->id && $preOrder->shop_id !== $shop->id) {
            throw new HttpResponseException(
                $this->json(__('You are not allowed to access this order'), [], 403)
            );
        }

        return $preOrder;
    }

    /**
     * Parse the from/to date filter (defaults to the last 30 days).
     */
    private function dateRange(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from) : now()->subDays(30);
        $to = $request->to ? Carbon::parse($request->to) : now();

        return [$from->startOfDay(), $to->endOfDay()];
    }

    /**
     * Ensure the shop is allowed to use the pre-order feature.
     */
    private function authorizePreOrder(): void
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('preOrderForShop', $setting);
    }
}
