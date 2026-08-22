<?php

namespace Modules\PreOrder\App\Http\Controllers;

use App\Exports\TemplateExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Modules\PreOrder\App\Repositories\PreOrderRepository;

class EarningReportController extends Controller
{

    public function commissionList()
    {
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to']   = request('to') ?? now()->format('m/d/Y');
        $shopId = generaleSetting('shop')?->id;
        $from   = Carbon::parse($data['from'])->startOfDay();
        $to     = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $isRoot = $shop->id === $rootShop->id ? true : false;
        $baseQuery = PreOrderRepository::query()
            ->delivered()
            ->when($isRoot, function ($q) use ($shopId) {
                $q->where('pre_orders.shop_id', '!=', $shopId);
            })
            ->when(!$isRoot, function ($q) use ($shopId) {
                $q->where('pre_orders.shop_id', $shopId);
            })
            ->join('pre_order_items', 'pre_orders.id', '=', 'pre_order_items.pre_order_id');
        $data['preOrders'] = (clone $baseQuery)
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->when($search, fn($q) => $q->filter(['search' => $search]))
            ->groupBy(
                'pre_orders.id',
                'pre_orders.admin_commission',
                'pre_orders.created_at',
                'pre_orders.order_code'
            )
            ->selectRaw('
                pre_orders.id,
                pre_orders.admin_commission,
                pre_orders.created_at,
                pre_orders.order_code,
                SUM(pre_order_items.quantity) AS total_product,
                (SUM(pre_order_items.quantity * pre_order_items.price)- pre_orders.admin_commission) AS sale_earning
            ')
            ->orderByDesc('sale_earning')
            ->paginate(30);
        $data['totalOrder'] = (clone $baseQuery)
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->selectRaw('
                SUM(pre_orders.admin_commission) AS totalAdminCommission,
                SUM(pre_order_items.quantity * pre_order_items.price) AS totalOrderAmount,
                (SUM(pre_order_items.quantity * pre_order_items.price)- SUM(pre_orders.admin_commission)) AS totalEarning
            ')->first();
        return view('preorder::Report.commissionList', $data);
    }

    /**
     * Per-shop profit report for delivered pre-orders. Each shop sees only its
     * own orders. Profit is taken straight from the pre_order_items table, which
     * already stores each line's buying price and selling price.
     */
    public function profitReport()
    {
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to']   = request('to') ?? now()->format('m/d/Y');
        $shopId = generaleSetting('shop')?->id;
        $from   = Carbon::parse($data['from'])->startOfDay();
        $to     = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $baseQuery = PreOrderRepository::query()
            ->delivered()
            ->where('pre_orders.shop_id', $shopId)
            ->join('pre_order_items', 'pre_orders.id', '=', 'pre_order_items.pre_order_id')
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->when($search, fn($q) => $q->filter(['search' => $search]));
        $data['preOrders'] = (clone $baseQuery)
            ->groupBy(
                'pre_orders.id',
                'pre_orders.created_at',
                'pre_orders.order_code'
            )
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
            ->paginate(30);
        $data['totalReport'] = (clone $baseQuery)
            ->selectRaw('
                SUM(pre_order_items.quantity * pre_order_items.price) AS totalSale,
                SUM(pre_order_items.quantity * pre_order_items.buying_price) AS totalBuying,
                SUM(pre_order_items.quantity * (pre_order_items.price - pre_order_items.buying_price)) AS totalProfit
            ')->first();
        return view('preorder::Report.profitReport', $data);
    }

    /**
     * Excel export of the per-shop profit report (same scope/filters as the
     * profitReport screen).
     */
    public function profitReportExport()
    {
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to']   = request('to') ?? now()->format('m/d/Y');
        $shopId = generaleSetting('shop')?->id;
        $from   = Carbon::parse($data['from'])->startOfDay();
        $to     = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $preOrders = PreOrderRepository::query()
            ->delivered()
            ->where('pre_orders.shop_id', $shopId)
            ->join('pre_order_items', 'pre_orders.id', '=', 'pre_order_items.pre_order_id')
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->when($search, fn($query) => $query->filter(['search' => $search]))
            ->groupBy(
                'pre_orders.id',
                'pre_orders.created_at',
                'pre_orders.order_code'
            )
            ->selectRaw('
                pre_orders.created_at,
                pre_orders.order_code,
                SUM(pre_order_items.quantity) AS total_product,
                SUM(pre_order_items.quantity * pre_order_items.price) AS sale_amount,
                SUM(pre_order_items.quantity * pre_order_items.buying_price) AS buying_amount,
                SUM(pre_order_items.quantity * (pre_order_items.price - pre_order_items.buying_price)) AS profit
            ')
            ->orderByDesc('pre_orders.created_at')
            ->get();
        $exportData = collect([
            [
                'SL',
                'Pre Order ID',
                'Created Date',
                'Total Product',
                'Sale Amount',
                'Buying Cost',
                'Profit',
            ],
        ]);
        $serialNo = 1;
        foreach ($preOrders ?? [] as $item) {
            $exportData->push([
                $serialNo++,
                $item->order_code,
                Carbon::parse($item->created_at)->format('d F, Y'),
                $item->total_product,
                round((float) $item->sale_amount, 2),
                round((float) $item->buying_amount, 2),
                round((float) $item->profit, 2),
            ]);
        }
        $timeStamp = date('Y-m-d_H-i-s');
        return Excel::download(
            new TemplateExport($exportData),
            'preorder_profit_report_' . $timeStamp . '.xlsx'
        );
    }

    public function commissionListExport()
    {
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to']   = request('to') ?? now()->format('m/d/Y');
        $shopId = generaleSetting('shop')?->id;
        $from   = Carbon::parse($data['from'])->startOfDay();
        $to     = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $isRoot = $shop->id === $rootShop->id ? true : false;
        $preOrders = PreOrderRepository::query()
            ->delivered()
            ->when($isRoot, function ($q) use ($shopId) {
                $q->where('pre_orders.shop_id', '!=', $shopId);
            })
            ->when(!$isRoot, function ($q) use ($shopId) {
                $q->where('pre_orders.shop_id', $shopId);
            })
            ->join('pre_order_items', 'pre_orders.id', '=', 'pre_order_items.pre_order_id')
            ->whereBetween('pre_orders.created_at', [$from, $to])
            ->when($search, fn($query) => $query->filter(['search' => $search]))
            ->groupBy(
                'pre_orders.id',
                'pre_orders.admin_commission',
                'pre_orders.created_at',
                'pre_orders.order_code'
            )
            ->selectRaw('
                pre_orders.admin_commission,
                pre_orders.created_at,
                pre_orders.order_code,
                SUM(pre_order_items.quantity) AS total_product,
                (SUM(pre_order_items.quantity * pre_order_items.price)- pre_orders.admin_commission) AS sale_earning
            ')
            ->orderByDesc('sale_earning')
            ->get();
        $exportData = collect([
            [
                'SL',
                'Pre Order ID',
                'Created Date',
                'Total Product',
                'Admin Commission',
                'Selling Earning'
            ],
        ]);
        $serialNo = 1;
        foreach ($preOrders ?? [] as $item) {
            $exportData->push([
                $serialNo++,
                $item->order_code,
                Carbon::parse($item->created_at)->format('d F, Y'),
                $item->total_product,
                round((float) $item->admin_commission, 2),
                round((float) $item->sale_earning, 2),
            ]);
        }
        $timeStamp = date('Y-m-d_H-i-s');
        return Excel::download(
            new TemplateExport($exportData),
            'preorder_commission_report_' . $timeStamp . '.xlsx'
        );
    }
}
