<?php

namespace Modules\Report\App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Withdraw;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Schema;
use App\Repositories\ShopSubscriptionRepository;

class EarningReportController extends Controller
{
    public function earningSummary()
    {
        $data['title'] = 'Earning Summary';
        $data['reportType'] = 'earningSummary';
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to'] = request('to') ?? now()->format('m/d/Y');
        $shop = generaleSetting('shop');
        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->endOfDay();
        $data['totalSale'] = OrderRepository::query()->withoutGlobalScopes()->where('shop_id', $shop->id)->where('order_status', '!=', OrderStatus::CANCELLED->value)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');
        $data['deliveryFee'] = OrderRepository::query()->withoutGlobalScopes()->where('shop_id', $shop->id)
            ->whereBetween('created_at', [$from, $to])
            ->sum('delivery_charge');
        $data['adminCommission'] = OrderRepository::query()->withoutGlobalScopes()
            ->where('shop_id', $shop->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->sum('admin_commission') ?? 0;
        $data['maintenanceFee'] = ShopSubscriptionRepository::query()
            ->where('shop_id', $shop->id)
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->sum('price') ?? 0;
        $data['totalPurchase'] = module_exists('Purchase') ? DB::table('purchases')->where('shop_id', $shop->id)->whereBetween('created_at', [$from, $to])->sum('total_amount') : 0;
        $data['totalRefundAmount'] = Schema::hasTable('return_order_details')
            ? DB::table('return_order_details')
            ->join('products', 'products.id', '=', 'return_order_details.product_id')
            ->where('products.shop_id', $shop->id)
            ->whereBetween('return_order_details.created_at', [$from, $to])
            ->sum(DB::raw('return_order_details.quantity * return_order_details.price'))
            : 0;
        $data['totalEarningProfit'] = $data['totalSale'] - ($data['deliveryFee'] + $data['adminCommission'] + $data['maintenanceFee'] + $data['totalPurchase'] + $data['totalRefundAmount']);
        return view('report::earningReport.earningSummary', $data);
    }


    public function commissionList()
    {
        $data['title'] = 'Commission List';
        $data['reportType'] = 'commissionList';
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to']   = request('to') ?? now()->format('m/d/Y');
        $shop   = generaleSetting('shop');
        $from   = Carbon::parse($data['from'])->startOfDay();
        $to     = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $baseQuery = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('orders.shop_id', $shop->id)
            ->join('order_products', 'orders.id', '=', 'order_products.order_id');
        $data['orders'] = (clone $baseQuery)
            ->whereBetween('orders.created_at', [$from, $to])
            ->when($search, fn($q) => $q->filter(['search' => $search]))
            ->groupBy(
                'orders.id',
                'orders.admin_commission',
                'orders.created_at',
                'orders.order_code',
                'orders.prefix'
            )
            ->selectRaw('
            orders.id,
            orders.admin_commission,
            orders.created_at,
            orders.order_code,
            orders.prefix,
            SUM(order_products.quantity) AS total_quantity,
            SUM(order_products.quantity * order_products.price) AS total_sale_amount,
            SUM(order_products.quantity * order_products.buying_price) AS total_buying_amount,
            (
                SUM(order_products.quantity * order_products.price)
                - SUM(order_products.quantity * order_products.buying_price)
                - orders.admin_commission
            ) AS profit
        ')
            ->orderByDesc('profit')
            ->paginate(30);

        $data['totalOrder'] = (clone $baseQuery)
            ->selectRaw('
            SUM(orders.admin_commission) AS totalAdminCommission,
            SUM(order_products.quantity * order_products.price) AS totalOrderAmount,
            (
                SUM(order_products.quantity * order_products.price)
                - SUM(order_products.quantity * order_products.buying_price)
                - SUM(orders.admin_commission)
            ) AS totalProfit
        ')
            ->first();

        return view('report::earningReport.commissionList', $data);
    }


    public function payOut()
    {
        $data['title'] = 'PayOut';
        $data['reportType'] = 'payOut';
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to']   = request('to') ?? now()->format('m/d/Y');
        $shop = generaleSetting('shop');
        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();
        $orderBaseQuery = OrderRepository::query()
            ->withoutGlobalScopes()
            ->completed()
            ->where('shop_id', $shop->id);
        // orders
        $data['orders'] = (clone $orderBaseQuery)
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('
            SUM(admin_commission) AS adminCommission,
            SUM(delivery_charge) AS riderCost
        ')
            ->first();
        $data['totalAdminCommission'] = (clone $orderBaseQuery)->sum('admin_commission');
        $data['totalRiderCost']       = (clone $orderBaseQuery)->sum('delivery_charge');
        // Refunds
        $data['refundAmount'] = 0;
        $data['totalRefundAmount'] = 0;
        if (Schema::hasTable('return_order_details')) {

            $refundBase = DB::table('return_order_details')
                ->join('products', 'products.id', '=', 'return_order_details.product_id')
                ->where('products.shop_id', $shop->id);

            $data['refundAmount'] = (clone $refundBase)
                ->whereBetween('return_order_details.created_at', [$from, $to])
                ->sum(DB::raw('return_order_details.quantity * return_order_details.price'));

            $data['totalRefundAmount'] = (clone $refundBase)
                ->sum(DB::raw('return_order_details.quantity * return_order_details.price'));
        }
        // Maintenance fee
        $subscriptionQuery = ShopSubscriptionRepository::query()
            ->where('shop_id', $shop->id);
        $data['maintenanceFee'] = (clone $subscriptionQuery)
            ->whereBetween('created_at', [$from, $to])
            ->sum('price');
        $data['totalMaintenanceFee'] = (clone $subscriptionQuery)->sum('price');
        // Withdrawals
        $withdrawQuery = Withdraw::query()
            ->where('shop_id', $shop->id)
            ->where('status', 'approved');
        $data['withdrawal'] = (clone $withdrawQuery)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');
        $data['totalWithdrawal'] = (clone $withdrawQuery)->sum('amount');
        return view('report::earningReport.payOut', $data);
    }
}
