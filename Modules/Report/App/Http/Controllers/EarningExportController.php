<?php

namespace Modules\Report\App\Http\Controllers;

use Carbon\Carbon;
use App\Enums\OrderStatus;
use App\Exports\TemplateExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\OrderRepository;

class EarningExportController extends Controller
{
    public function commissionListExport()
    {
        $data['from'] = request('from') ?? now()->subDays(30)->format('m/d/Y');
        $data['to'] = request('to') ?? now()->format('m/d/Y');
        $shop = generaleSetting('shop');
        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->endOfDay();
        $search = request('search');
        $orders = OrderRepository::query()
            ->withoutGlobalScopes()
            ->join('order_products', 'orders.id', '=', 'order_products.order_id')
            ->where('orders.shop_id', $shop->id)
            ->where('orders.order_status', OrderStatus::DELIVERED->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->when($search, fn($query) => $query->filter(['search' => $search]))
            ->groupBy('orders.admin_commission', 'orders.created_at', 'order_code', 'prefix')
            ->selectRaw('
                    orders.admin_commission,
                    orders.created_at,
                    orders.order_code,
                    orders.prefix,
                    SUM(order_products.quantity) AS total_quantity,
                    SUM(order_products.quantity * order_products.price) AS total_sale_amount,
                    SUM(order_products.quantity * order_products.buying_price) AS total_buying_amount,
                    (
                        SUM(order_products.quantity * order_products.price)
                        - SUM(order_products.quantity * order_products.buying_price) - orders.admin_commission
                    ) AS profit
                ')
            ->orderByDesc('profit')
            ->get();
        $exportData = collect(
            [
                [
                    'SL',
                    'Order ID',
                    'Created Date',
                    'Buying Amount',
                    'Selling  Amount',
                    'Admin Commission',
                    'Selling Earning'
                ],
            ]
        );
        $serialNo = 1;
        foreach ($orders ?? [] as $item) {
            $exportData->push([
                $serialNo++,
                $item->prefix . $item->order_code,
                $item->created_at->format('d F, Y'),
                round((float) $item->total_buying_amount, 2),
                round((float) $item->total_sale_amount, 2),
                round((float) $item->admin_commission, 2),
                round((float) $item->profit, 2)
            ]);
        }
        $timeStamp = date('Y-m-d_H-i-s');
        return Excel::download(new TemplateExport($exportData), 'commission_report_' . $timeStamp . '.xlsx');
    }
}
