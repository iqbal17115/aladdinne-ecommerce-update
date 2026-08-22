<?php

namespace App\Http\Controllers\API;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FlashSaleResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\SupportItemResource;
use App\Models\Ad;
use App\Models\GeneraleSetting;
use App\Models\SupportItem;
use App\Models\User;
use App\Repositories\BannerRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\FlashSaleRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ShopRepository;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Index method for retrieving banners, categories, and popular products.
     *
     * @return Some_Return_Value
     */
    public function index(Request $request)
    {
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 8;
        $skip = ($page * $perPage) - $perPage;
        $generaleSetting = generaleSetting('setting');
        $rootShop = generaleSetting('rootShop');
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = $rootShop;
        }

        $banners = BannerRepository::query()->whereNull('shop_id')->active()->get();

        $categories = CategoryRepository::query()->active()
            ->whereHas('shops', function ($query) use ($rootShop) {
                return $query->where('shop_id', $rootShop->id);
            })->whereHas('products', function ($product) {
                return $product->where('is_active', true);
            })->orderBy('id')->take(12)->get();

        $popularProducts = ProductRepository::query()->isActive()
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })->withCount('orders as orders_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->orderByDesc('average_rating')
            ->orderByDesc('orders_count')
            ->take(6)->get();

        $bestDealProducts = ProductRepository::query()->isActive()
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })->withSum('orders as orders_count', 'order_products.quantity')
            ->orderByDesc('orders_count')
            ->take(6)->get();

        $justForYou = ProductRepository::query()->isActive()->latest('id')->when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop->id);
        });
        $total = $justForYou->count();
        $justForYou = $justForYou->skip($skip)->take($perPage)->get();

        $shops = collect([]);

        if ($generaleSetting?->shop_type != 'single') {
            $shops = ShopRepository::query()->isActive()->whereHas('products', function ($query) {
                return $query->isActive();
            })->withCount('orders')->withAvg('reviews as average_rating', 'rating')->orderByDesc('average_rating')->orderByDesc('orders_count')->take(8)->get();
        }

        $ads = Ad::where('status', 1)->latest('id')->take(2)->get();

        $supportItems = SupportItem::where('is_active', true)->orderBy('order')->get();

        // get incoming flash sale
        $incomingFlashSale = FlashSaleRepository::getIncoming();

        // get running flash sale
        $runningFlashSale = FlashSaleRepository::getRunning();

        // in demo mode, always show the first flash sale as running
        // and the last one as incoming (demo data dates may be outdated)
        if (app()->environment('local')) {
            $runningFlashSale = FlashSaleRepository::query()->where('status', true)->oldest('id')->first();
            $incomingFlashSale = FlashSaleRepository::query()->where('status', true)->latest('id')->first();
        }

        return $this->json('home', [
            'banners' => BannerResource::collection($banners),
            'ads' => BannerResource::collection($ads),
            'support_items' => SupportItemResource::collection($supportItems),
            'categories' => CategoryResource::collection($categories),
            'shops' => ShopResource::collection($shops),
            'popular_products' => ProductResource::collection($popularProducts),
            'best_deal_products' => ProductResource::collection($bestDealProducts),
            'just_for_you' => [
                'total' => $total,
                'products' => ProductResource::collection($justForYou),
            ],
            'incoming_flash_sale' => $incomingFlashSale ? FlashSaleResource::make($incomingFlashSale) : null,
            'running_flash_sale' => $runningFlashSale ? FlashSaleResource::make($runningFlashSale)->toArray(request(), 'true', 'true') : null,
        ]);
    }

    /**
     * Get recently viewed products for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentlyViews()
    {
        $generaleSetting = GeneraleSetting::first();

        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = User::role(Roles::ROOT->value)->first()?->shop;
        }

        /**
         * @var \App\Models\User $user
         */
        $user = auth()->user();

        $products = $user->recentlyViewedProducts()->when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop->id);
        })->where('is_active', true)->orderBy('pivot_updated_at', 'desc')->take(10)->get();

        return $this->json('recently viewed products', [
            'products' => ProductResource::collection($products),
        ]);
    }
}
