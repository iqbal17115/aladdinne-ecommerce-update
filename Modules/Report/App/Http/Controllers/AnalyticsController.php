<?php

namespace Modules\Report\App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Schema;
use App\Repositories\ShopSubscriptionRepository;

class AnalyticsController extends Controller
{

    public function index()
    {
        $shop = generaleSetting('shop');
        $data['totalSaleAmount'] = OrderRepository::query()->completed()->where('shop_id', $shop->id)->sum('total_amount');
        $data['totalCancelAmount'] = OrderRepository::query()->cancelled()->where('shop_id', $shop->id)->sum('total_amount');
        $data['totalPurchaseAmount'] = module_exists('Purchase') ? DB::table('purchases')->where('shop_id', $shop->id)->sum('total_amount') : 0;
        $data['stockProductAmount'] = Product::query()->isActive()->where('shop_id', $shop->id)
            ->selectRaw('SUM(quantity * price) AS total_amount')
            ->value('total_amount');
        $data['totalRefundAmount'] = Schema::hasTable('return_order_details')
            ? DB::table('return_order_details')
            ->join('products', 'products.id', '=', 'return_order_details.product_id')
            ->where('products.shop_id', $shop->id)
            ->sum(DB::raw('return_order_details.quantity * return_order_details.price'))
            : 0;
        $data['totalCategories'] = Category::query()->active()->count();
        $data['totalBrand'] = Brand::query()->isActive()->count();
        $data['topSellingProducts'] = Product::query()->isActive()->where('shop_id', $shop->id)->withCount('orders')->orderBy('orders_count', 'desc')->limit(5)->get();
        $data['topSellingCategories'] = Category::query()
            ->whereHas('products', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id)
                    ->whereHas('orders');
            })
            ->withCount([
                'products as sell_count' => function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id)
                        ->join('order_products', 'products.id', '=', 'order_products.product_id');
                }
            ])
            ->orderByDesc('sell_count')
            ->limit(5)
            ->get();
        $data['topSellingBrands'] = Brand::query()
            ->whereHas('products', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id)
                    ->whereHas('orders');
            })
            ->withCount([
                'products as sell_count' => function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id)
                        ->join('order_products', 'products.id', '=', 'order_products.product_id');
                }
            ])
            ->orderByDesc('sell_count')
            ->limit(5)
            ->get();
        $data['maintenanceFee'] = ShopSubscriptionRepository::query()
            ->where('shop_id', $shop->id)
            ->sum('price') ?? 0;
        $data['withdrawalAmount'] = Withdraw::query()->where('shop_id', $shop->id)->where('status', 'approved')->sum('amount') ?? 0;
        $data['refundAmount'] = Schema::hasTable('return_order_details') ? DB::table('return_order_details')
            ->join('products', 'products.id', '=', 'return_order_details.product_id')
            ->where('products.shop_id', $shop->id)
            ->sum(DB::raw('return_order_details.quantity * return_order_details.price')) : 0;
        $data['orderPayout'] = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('shop_id', $shop->id)
            ->selectRaw('SUM(admin_commission + delivery_charge) AS totalOrderPayout')->first();
        $totalOrderPayout = $data['orderPayout']->totalOrderPayout ?? 0;
        $data['payOuts'] = $data['maintenanceFee'] + $data['withdrawalAmount'] + $data['refundAmount'] + $totalOrderPayout;
        $data['websiteSellEarning'] = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('shop_id', $shop->id)
            ->where('pos_order', false)
            ->selectRaw('SUM(total_amount) AS totalOrderPayout')->first();
        $data['posSellEarning'] = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('shop_id', $shop->id)
            ->where('pos_order', true)
            ->selectRaw('SUM(total_amount) AS totalOrderPayout')->first();

        return view('report::index', $data);
    }

    public function saleAnalytics(Request $request)
    {
        $type = $request->type ?? 'daily';
        $shopId = generaleSetting('shop')?->id;
        $currentYear = now()->year;
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = now()->endOfWeek();
        $query = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('shop_id', $shopId);
        switch ($type) {
            case 'daily':
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $keyMapper = fn($row) => $labels[$row->day_number - 1];
                $selectRaw = "
                WEEKDAY(created_at) + 1 as day_number,
                SUM(CASE WHEN pos_order = 0 THEN total_amount ELSE 0 END) as website_total,
                SUM(CASE WHEN pos_order = 1 THEN total_amount ELSE 0 END) as pos_total
            ";
                $groupBy = ['day_number'];
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                break;
            case 'monthly':
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $keyMapper = fn($row) => $labels[$row->month_number - 1];
                $selectRaw = "
                MONTH(created_at) as month_number,
                SUM(CASE WHEN pos_order = 0 THEN total_amount ELSE 0 END) as website_total,
                SUM(CASE WHEN pos_order = 1 THEN total_amount ELSE 0 END) as pos_total
            ";
                $groupBy = ['month_number'];
                $query->whereYear('created_at', $currentYear);
                break;
            default: // yearly
                $labels = [];
                for ($y = $currentYear; $y >= $currentYear - 6; $y--) {
                    $labels[] = (string)$y;
                }
                $keyMapper = fn($row) => (string)$row->year;
                $selectRaw = "
                YEAR(created_at) as year,
                SUM(CASE WHEN pos_order = 0 THEN total_amount ELSE 0 END) as website_total,
                SUM(CASE WHEN pos_order = 1 THEN total_amount ELSE 0 END) as pos_total
            ";
                $groupBy = ['year'];
                $query->whereYear('created_at', '>=', $currentYear - 6);
        }
        $data = $query->selectRaw($selectRaw)
            ->groupBy(...$groupBy)
            ->get();
        $website = array_fill_keys($labels, 0);
        $pos = array_fill_keys($labels, 0);
        foreach ($data as $row) {
            $key = $keyMapper($row);
            if (!isset($website[$key])) continue;
            $website[$key] = round((float)$row->website_total, 2);
            $pos[$key] = round((float)$row->pos_total, 2);
        }
        return response()->json([
            'labels' => $labels,
            'website' => $website,
            'pos' => $pos,
        ]);
    }



    public function costAnalytics()
    {
        $shopId = generaleSetting('shop')?->id;
        $maintenanceFee = ShopSubscriptionRepository::query()->where('shop_id', $shopId)->sum('price') ?? 0;
        $withdrawalAmount = Withdraw::where('shop_id', $shopId)
            ->where('status', 'approved')
            ->sum('amount') ?? 0;
        $payOuts = $maintenanceFee + $withdrawalAmount;
        $rider = OrderRepository::query()->withoutGlobalScopes()
            ->completed()
            ->where('shop_id', $shopId)
            ->sum('delivery_charge') ?? 0;
        $refund = 0;
        if (Schema::hasTable('return_order_details')) {
            $refund = DB::table('return_order_details')
                ->join('products', 'products.id', '=', 'return_order_details.product_id')
                ->where('products.shop_id', $shopId)
                ->sum(DB::raw('return_order_details.quantity * return_order_details.price'));
        }
        $purchase = module_exists('Purchase')
            ? DB::table('purchases')->where('shop_id', $shopId)->sum('total_amount')
            : 0;
        $grantTotal = $payOuts + $rider + $refund + $purchase;
        return response()->json([
            'Payouts' => $payOuts,
            'Rider' => $rider,
            'Refund' => $refund,
            'Purchase' => $purchase,
            'grantTotal' => $grantTotal
        ]);
    }

    public function earningAnalytics(Request $request)
    {
        $type   = $request->type ?? 'daily';
        $shopId = generaleSetting('shop')?->id;
        $currentYear = now()->year;
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = now()->endOfWeek();
        // Initialize variables
        $labels = [];
        $mapFunc = fn($grp) => $grp - 1; // default for daily
        $orderDate = $purchaseDate = $payoutDate = null;

        switch ($type) {
            case 'daily':
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $orderDate    = "WEEKDAY(orders.created_at)+1";
                $purchaseDate = "WEEKDAY(purchases.created_at)+1";
                $payoutDate   = "WEEKDAY(withdraws.created_at)+1";
                $orderFilter    = fn($q) => $q->whereBetween('orders.created_at', [$startOfWeek, $endOfWeek]);
                $purchaseFilter = fn($q) => $q->whereBetween('purchases.created_at', [$startOfWeek, $endOfWeek]);
                $payoutFilter   = fn($q) => $q->whereBetween('withdraws.created_at', [$startOfWeek, $endOfWeek]);
                break;

            case 'monthly':
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $orderDate    = "MONTH(orders.created_at)";
                $purchaseDate = "MONTH(purchases.created_at)";
                $payoutDate   = "MONTH(withdraws.created_at)";
                $orderFilter    = fn($q) => $q->whereYear('orders.created_at', $currentYear);
                $purchaseFilter = fn($q) => $q->whereYear('purchases.created_at', $currentYear);
                $payoutFilter   = fn($q) => $q->whereYear('withdraws.created_at', $currentYear);
                break;

            case 'yearly':
            default:
                for ($y = $currentYear; $y >= $currentYear - 6; $y--) $labels[] = (string)$y;
                $orderDate    = "YEAR(orders.created_at)";
                $purchaseDate = "YEAR(purchases.created_at)";
                $payoutDate   = "YEAR(withdraws.created_at)";
                $mapFunc = fn($grp) => array_search((string)$grp, $labels);
                $orderFilter    = fn($q) => $q->whereYear('orders.created_at', '>=', $currentYear - 6);
                $purchaseFilter = fn($q) => $q->whereYear('purchases.created_at', '>=', $currentYear - 6);
                $payoutFilter   = fn($q) => $q->whereYear('withdraws.created_at', '>=', $currentYear - 6);
                break;
        }

        /* -------- order and sale -------- */
        $orders = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('orders.shop_id', $shopId)
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->tap($orderFilter)
            ->selectRaw("
            $orderDate as grp,
            SUM(orders.total_amount) as total_orders,
            SUM(order_products.price * order_products.quantity) as total_sales
        ")
            ->groupBy('grp')
            ->get();

        /* -------- purchase -------- */
        $purchases = module_exists('Purchase')
            ? DB::table('purchases')
            ->where('shop_id', $shopId)
            ->tap($purchaseFilter)
            ->selectRaw("$purchaseDate as grp, SUM(total_amount) as total_purchase")
            ->groupBy('grp')
            ->get()
            : collect();

        /* -------- payout (Withdraw + Refund) -------- */
        $withdraws = DB::table('withdraws')
            ->where('shop_id', $shopId)
            ->where('status', 'approved')
            ->tap($payoutFilter)
            ->selectRaw("$payoutDate as grp, SUM(amount) as total_withdraw")
            ->groupBy('grp')
            ->get();
        $refunds = Schema::hasTable('return_order_details')
            ? DB::table('return_order_details')
            ->join('products', 'products.id', '=', 'return_order_details.product_id')
            ->where('products.shop_id', $shopId)
            ->tap($payoutFilter)
            ->selectRaw("$payoutDate as grp, SUM(return_order_details.quantity * return_order_details.price) as total_refund")
            ->groupBy('grp')
            ->get()
            : collect();

        // Merge withdraws + refunds for payouts
        $payoutData = [];
        foreach ($withdraws as $w) {
            $grp = $w->grp;
            $payoutData[$grp] = ($payoutData[$grp] ?? 0) + (float)$w->total_withdraw;
        }
        foreach ($refunds as $r) {
            $grp = $r->grp;
            $payoutData[$grp] = ($payoutData[$grp] ?? 0) + (float)$r->total_refund;
        }
        /* -------- map data to chart -------- */
        $mapToChart = function ($data, $labels, $valueKey = null) use ($mapFunc) {
            $arr = array_fill(0, count($labels), 0);
            foreach ($data as $item) {
                $i = $mapFunc($item->grp);
                if ($i !== false && $i !== null) {
                    $arr[$i] = round($valueKey ? (float)$item->$valueKey : (float)$item, 2);
                }
            }
            return $arr;
        };
        $salesArr    = $mapToChart($orders, $labels, 'total_sales');
        $ordersArr   = $mapToChart($orders, $labels, 'total_orders');
        $purchaseArr = $mapToChart($purchases, $labels, 'total_purchase');
        $payoutArr   = $mapToChart(collect($payoutData)->map(fn($v, $k) => (object)['grp' => $k, 'value' => $v]), $labels, 'value');
        return response()->json([
            'labels'   => $labels,
            'sales'    => array_values($salesArr),
            'orders'   => array_values($ordersArr),
            'purchase' => array_values($purchaseArr),
            'payouts'  => array_values($payoutArr),
        ]);
    }
}
