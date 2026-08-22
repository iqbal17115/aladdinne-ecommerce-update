<?php

namespace Modules\Report\App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Favorite;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CategoryRepository;
use Modules\Report\App\Repositories\ProductSearchLogRepository;

class ReportController extends Controller
{
    public function saleProductReport(Request $request)
    {
        $data['categories'] = Cache::remember('categories_all', now()->addHour(), function () {
            return CategoryRepository::query()->get();
        });
        $data['reportType'] = 'saleProduct';
        $shop = generaleSetting('shop');
        $startDate = null;
        $endDate = null;
        $dateRange = $request->date_range;
        $categoryId = $request->category_id;
        $search = $request->search;
        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$startDate, $endDate] = explode(' - ', $dateRange, 2);
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate))->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay();
            } catch (\Exception $e) {
                $startDate = $endDate = null;
            }
        }
        $data['orderProducts'] = OrderProduct::query()
            ->join('products', 'products.id', '=', 'order_products.product_id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('products.shop_id', $shop->id)
            ->whereNotIn('orders.order_status', ['Cancelled', 'Returned'])
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('order_products.created_at', [$startDate, $endDate]);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('products.name', 'LIKE', "%{$search}%");
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereExists(function ($sub) use ($categoryId) {
                    $sub->selectRaw(1)
                        ->from('product_categories')
                        ->whereColumn('product_categories.product_id', 'products.id')
                        ->where('product_categories.category_id', $categoryId);
                });
            })
            ->selectRaw('
                order_products.product_id,
                products.name AS product_name,
                SUM(order_products.quantity) AS total_quantity,
                SUM(order_products.quantity * order_products.price) AS total_sale_amount,
                SUM(order_products.quantity * order_products.buying_price) AS total_buying_amount,
                (
                    SUM(order_products.quantity * order_products.price)
                    - SUM(order_products.quantity * order_products.buying_price)
                ) AS profit
            ')
            ->groupBy('order_products.product_id', 'products.name')
            ->orderByDesc('total_sale_amount')
            ->paginate(30);

        return view('report::report.saleProduct', $data);
    }
    public function wishListReport(Request $request)
    {
        $data['categories'] = Cache::remember('categories_all', now()->addHour(), function () {
            return CategoryRepository::query()->get();
        });
        $data['reportType'] = 'wishList';
        $shop = generaleSetting('shop');
        $startDate = null;
        $endDate   = null;
        $dateRange = $request->date_range;
        $categoryId = $request->category_id;
        $search = $request->search;
        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$startDate, $endDate] = explode(' - ', $dateRange, 2);
            try {
                $dateRange = explode(" - ", $dateRange);
                $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate))->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay();
            } catch (\Exception $e) {
                $startDate = $endDate = null;
            }
        }
        $data['wishList'] = Favorite::query()
            ->join('products', 'products.id', '=', 'favorites.product_id')
            ->where('products.shop_id', $shop->id)
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('favorites.created_at', [$startDate, $endDate]);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('products.name', 'LIKE', "%{$search}%");
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereExists(function ($sub) use ($categoryId) {
                    $sub->selectRaw(1)
                        ->from('product_categories')
                        ->whereColumn('product_categories.product_id', 'products.id')
                        ->where('product_categories.category_id', $categoryId);
                });
            })
            ->selectRaw('
                favorites.product_id,
                products.name AS product_name,
                products.quantity AS total_stock,
                count(favorites.id) AS total_quantity
            ')
            ->groupBy('favorites.product_id', 'products.name', 'products.quantity')
            ->orderByDesc('total_quantity')
            ->paginate(30);
        return view('report::report.wishlist', $data);
    }
    public function addToCartReport(Request $request)
    {
        $data['categories'] = Cache::remember('categories_all', now()->addHour(), function () {
            return CategoryRepository::query()->get();
        });
        $data['reportType'] = 'addToCart';
        $shop = generaleSetting('shop');
        $startDate = null;
        $endDate   = null;
        $dateRange = $request->date_range;
        $categoryId = $request->category_id;
        $search = $request->search;
        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$startDate, $endDate] = explode(' - ', $dateRange, 2);
            try {
                $dateRange = explode(" - ", $dateRange);
                $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate))->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay();
            } catch (\Exception $e) {
                $startDate = $endDate = null;
            }
        }
        $data['addToCart'] = CartRepository::query()
            ->join('products', 'products.id', '=', 'carts.product_id')
            ->where('products.shop_id', $shop->id)
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('carts.created_at', [$startDate, $endDate]);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('products.name', 'LIKE', "%{$search}%");
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereExists(function ($sub) use ($categoryId) {
                    $sub->selectRaw(1)
                        ->from('product_categories')
                        ->whereColumn('product_categories.product_id', 'products.id')
                        ->where('product_categories.category_id', $categoryId);
                });
            })
            ->selectRaw('
                carts.product_id,
                products.name AS product_name,
                products.quantity AS total_stock,
                SUM(carts.quantity) AS total_quantity
            ')
            ->groupBy('carts.product_id', 'products.name', 'products.quantity')
            ->orderByDesc('total_quantity')
            ->paginate(30);

        return view('report::report.addToCart', $data);
    }
    public function productSearchReport(Request $request)
    {
        $data['reportType'] = 'productSearch';
        $startDate = null;
        $endDate   = null;
        $dateRange = $request->date_range;
        $search = $request->search;
        if ($dateRange && str_contains($dateRange, ' - ')) {
            [$startDate, $endDate] = explode(' - ', $dateRange, 2);
            try {
                $dateRange = explode(" - ", $dateRange);
                $startDate = Carbon::createFromFormat('Y-m-d', trim($startDate))->startOfDay();
                $endDate   = Carbon::createFromFormat('Y-m-d', trim($endDate))->endOfDay();
            } catch (\Exception $e) {
                $startDate = $endDate = null;
            }
        }
        $data['productSearch'] = ProductSearchLogRepository::query()
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('product_search_logs.created_at', [$startDate, $endDate]);
            })
            ->when($search, function ($q) use ($search) {
                $q->where('product_search_logs.keyword', 'LIKE', "%{$search}%");
            })
            ->selectRaw('
                product_search_logs.keyword,
                SUM(product_search_logs.total_search) AS total_quantity
            ')
            ->groupBy('product_search_logs.keyword')
            ->orderByDesc('total_quantity')
            ->paginate(30);

        return view('report::report.productSearch', $data);
    }
}
