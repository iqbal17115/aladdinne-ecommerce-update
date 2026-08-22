<?php

namespace Modules\PreOrder\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Scopes\PreOderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\PreOrder\App\Repositories\PreOrderProductRepository;
use Modules\PreOrder\App\Repositories\PreOrderRepository;

class AnalyticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shopId = generaleSetting('shop')?->id;
        $preOrderQuery = PreOrderRepository::query()
            ->where('shop_id', $shopId)
            ->selectRaw('
                SUM(payable_amount) as total_product_amount,
                SUM(paid_amount) as total_paid_amount,
                SUM(CASE WHEN order_status = "Requested" THEN 1 ELSE 0 END) as request_order,
                SUM(CASE WHEN order_status = "Accepted" THEN 1 ELSE 0 END) as accepted_order,
                SUM(CASE WHEN order_status = "Prepayment Request" THEN 1 ELSE 0 END) as pre_request_order,
                SUM(CASE WHEN order_status = "Confirmed Prepayment" THEN 1 ELSE 0 END) as confirmed_pre_request_order,
                SUM(CASE WHEN order_status = "Delivered" THEN 1 ELSE 0 END) as delivered_order,
                SUM(payable_amount * (order_status = "In Shipping")) as in_shipping_amount,
                SUM(payable_amount * (order_status = "Delivered")) as Delivered_amount,
                SUM(payable_amount -paid_amount * (order_status = "Delivered")) as delay_final_amount

            ')
            ->first();
        $data = [
            'totalSaleAmount' => (float) $preOrderQuery->total_product_amount,
            'totalPaidAmount' => (float) $preOrderQuery->total_paid_amount,
            'totalDueAmount' => (float) $preOrderQuery->total_product_amount - (float) $preOrderQuery->total_paid_amount,
            'requestOrder' => (int) $preOrderQuery->request_order,
            'acceptedOrder' => (int) $preOrderQuery->accepted_order,
            'preRequestOrder' => (int) $preOrderQuery->pre_request_order,
            'confirmedPreReqOrder' => (int) $preOrderQuery->confirmed_pre_request_order,
            'deliveredOrder' => (int) $preOrderQuery->delivered_order,
            'inShippingAmount' => (float) $preOrderQuery->in_shipping_amount,
            'deliveredAmount' => (float) $preOrderQuery->Delivered_amount,
            'delayFinalAmount' => (float) $preOrderQuery->delay_final_amount
        ];
        $productQuery = PreOrderProductRepository::query()->withoutGlobalScope(PreOderProduct::class)
            ->where('is_preorder', true)
            ->where('shop_id', $shopId);
        $data['totalProduct'] = $productQuery->count();
        $data['topProducts'] = DB::table('pre_order_items')
            ->join('products', 'pre_order_items.product_id', '=', 'products.id')
            ->where('products.shop_id', $shopId)
            ->selectRaw('
                products.id,
                products.name,
                products.product_thumbnail,
                products.brand_id,
                products.is_prepay,
                SUM(pre_order_items.quantity) as total_sold,
                SUM(pre_order_items.price * pre_order_items.quantity) as total_revenue
            ')
            ->groupBy(
                'products.id',
                'products.name',
                'products.product_thumbnail',
                'products.brand_id',
                'products.is_prepay'
            )
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();
        return view('preorder::index', $data);
    }
    public function saleAnalytics(Request $request)
    {
        $type = $request->type ?? 'monthly';
        $shopId = generaleSetting('shop')?->id;
        $currentYear = now()->year;
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = now()->endOfWeek();
        $query = PreOrderRepository::query()->where('shop_id', $shopId);
        switch ($type) {
            case 'daily':
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->selectRaw('WEEKDAY(created_at) + 1 as day_number, SUM(total_amount) as total_amount')
                    ->groupBy('day_number')
                    ->get();
                $keyMapper = fn($row) => $labels[$row->day_number - 1];
                break;
            case 'monthly':
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $query->whereYear('created_at', $currentYear)
                    ->selectRaw('MONTH(created_at) as month_number, SUM(total_amount) as total_amount')
                    ->groupBy('month_number');
                $keyMapper = fn($row) => $labels[$row->month_number - 1];
                break;
            default: // yearly
                $labels = collect(range($currentYear - 6, $currentYear))
                    ->map(fn($y) => (string)$y)
                    ->toArray();
                $query->whereYear('created_at', '>=', $currentYear - 6)
                    ->selectRaw('YEAR(created_at) as year, SUM(total_amount) as total_amount')
                    ->groupBy('year');
                $keyMapper = fn($row) => (string)$row->year;
        }
        $data = $query->get();
        $map = array_fill_keys($labels, 0);
        foreach ($data as $row) {
            $key = $keyMapper($row);
            if (isset($map[$key])) {
                $map[$key] = round((float)$row->total_amount, 2);
            }
        }
        return response()->json([
            'labels' => $labels,
            'website' => array_values($map)
        ]);
    }
}
