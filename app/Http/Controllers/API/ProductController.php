<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddFavoriteRequest;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ColorResource;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductSellerOfferResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\SizeResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Scopes\PreOderProduct;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Retrieve a paginated list of products based on the provided request parameters.
     *
     * @param  Request  $request  The request object containing page, per_page, and search parameters
     * @return Some_Return_Value The JSON response containing total and products data
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->page;
        // Page size comes straight from the client, so cap it — the storefront
        // only offers up to 100 and an unbounded value would load every product.
        $perPage = $request->per_page ? min((int) $request->per_page, 100) : $request->per_page;
        $skip = ($page * $perPage) - $perPage;

        $search = $request->search;
        $shopID = $request->shop_id;
        $categoryID = $request->category_id;
        $subCategoryID = $request->sub_category_id;

        $rating = $request->rating; // 4.0
        $sortType = $request->sort_type;
        $minPrice = $request->min_price;
        $maxPrice = $request->max_price;
        $brandID = $request->brand_id;
        $colorID = $request->color_id;
        $sizeID = $request->size_id;
        $isDigital = $request->is_digital == true ? true : false;

        module_exists('PreOrder') ? $isPreorder = (bool) $request->is_preorder : $isPreorder = false;

        $categoryID = $request->category_slug
            ? Category::where('slug', $request->category_slug)->value('id')
            : $categoryID;
        $subCategoryID = $request->sub_category_slug
            ? SubCategory::where('slug', $request->sub_category_slug)->value('id')
            : $subCategoryID;
        $brandID = $request->brand_slug
            ? Brand::where('slug', $request->brand_slug)->value('id')
            : $brandID;

        $generaleSetting = generaleSetting('setting');
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = generaleSetting('rootShop');
        }

        // get data for
        $rootShop = $shop ?? generaleSetting('rootShop');
        $productQuery = ProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop->id);
        })->isActive();

        $flashSale = FlashSale::isActive()->first();
        $flashSaleMinPrice = $flashSale ? $flashSale->products->min('pivot.price') : null;

        $productMinPrice = $productQuery->min('price');
        if ($flashSaleMinPrice && $flashSaleMinPrice < $productMinPrice) {
            $productMinPrice = $flashSaleMinPrice;
        }

        $productMaxPrice = $productQuery->max('price');
        $sizes = $rootShop?->sizes()->isActive()->get();
        $colors = $rootShop?->colors()->isActive()->get();
        $brands = $rootShop?->brands()->isActive()->get();

        if ($search && $search != '' && $search != null && strlen($search) >= 3) {
            $this->getSearchLog($search);
        }


        $preorderSetting = module_exists('PreOrder')
            ? \Modules\PreOrder\App\Models\PreOrderSetting::first()
            : null;

        $isPreorderMode = $preorderSetting
            ? app(\Modules\PreOrder\App\Policies\PreOrderPolicy::class)->canUsePreOrder(auth()->user() ?? new User(), $preorderSetting)
            : false;

        // filter query
        $products = ProductRepository::query()
            ->withoutGlobalScopes()
            ->when(!$isPreorderMode, fn($q) => $q->where('is_preorder', false))
            ->withSum('orders as orders_count', 'order_products.quantity')
            ->withAvg('reviews as average_rating', 'rating')
            ->isActive()
            ->when($isDigital, function ($query) {
                return $query->where('is_digital', true);
            })
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })->when($shopID && ! $shop, function ($query) use ($shopID) {
                return $query->where('shop_id', $shopID);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('short_description', 'like', '%' . $search . '%')
                        ->orWhere('code', 'like', '%' . $search . '%');
                });
            })->when($brandID, function ($query) use ($brandID) {
                return $query->where('brand_id', $brandID);
            })->when($isPreorder, function ($query) use ($isPreorder) {
                return $query->where('is_preorder', $isPreorder);
            })->when($colorID, function ($query) use ($colorID) {
                return $query->whereHas('colors', function ($query) use ($colorID) {
                    return $query->where('id', $colorID);
                });
            })->when($sizeID, function ($query) use ($sizeID) {
                return $query->whereHas('sizes', function ($query) use ($sizeID) {
                    return $query->where('id', $sizeID);
                });
            })->when($categoryID && ! $subCategoryID, function ($query) use ($categoryID) {
                return $query->whereHas('categories', function ($query) use ($categoryID) {
                    return $query->where('id', $categoryID);
                });
            })->when($subCategoryID, function ($query) use ($subCategoryID, $categoryID) {
                return $query->whereHas('subcategories', function ($query) use ($subCategoryID, $categoryID) {
                    return $query->where('id', $subCategoryID)
                        ->when($categoryID, function ($query) use ($categoryID) {
                            return $query->whereHas('categories', function ($query) use ($categoryID) {
                                return $query->where('id', $categoryID);
                            });
                        });
                });
            })->when($rating, function ($query) use ($rating) {
                $ratingValue = floatval($rating);
                $upperBound = $ratingValue + 1;

                return $query->havingRaw('average_rating >= ' . $rating . ' AND average_rating < ' . $upperBound);
            })->when($sortType == 'top_selling', function ($query) {
                return $query->orderByDesc('orders_count');
            })->when($sortType == 'popular_product', function ($query) {
                return $query->orderByDesc('orders_count')->orderByDesc('average_rating');
            })->when($sortType == 'newest' || $sortType == 'just_for_you', function ($query) {
                return $query->orderBy('id', 'desc');
            })->when($minPrice || $maxPrice, function ($query) use ($minPrice, $maxPrice) {
                $query->whereRaw('
                    COALESCE(
                        (SELECT flash_sale_products.price
                         FROM flash_sale_products
                         INNER JOIN flash_sales ON flash_sales.id = flash_sale_products.flash_sale_id
                         WHERE flash_sale_products.product_id = products.id
                         AND flash_sale_products.quantity > 0
                         AND flash_sales.status = 1
                         AND flash_sales.start_date <= CURDATE()
                         AND flash_sales.end_date >= CURDATE()
                         AND (flash_sales.start_time <= CURTIME() OR flash_sales.end_time >= CURTIME())
                         ORDER BY flash_sale_products.price ASC LIMIT 1
                        ),
                        IF(discount_price > 0, discount_price, price)
                    ) BETWEEN ? AND ?
                ', [$minPrice ?? 0, $maxPrice ?? PHP_INT_MAX]);
            })
            ->when(in_array($sortType, ['high_to_low', 'low_to_high']), function ($query) use ($sortType) {
                $order = $sortType === 'high_to_low' ? 'DESC' : 'ASC';

                return $query->orderByRaw("
                    COALESCE(
                        (SELECT flash_sale_products.price
                         FROM flash_sale_products
                         INNER JOIN flash_sales ON flash_sales.id = flash_sale_products.flash_sale_id
                         WHERE flash_sale_products.product_id = products.id
                         AND flash_sale_products.quantity > 0
                         AND flash_sales.status = 1
                         AND flash_sales.start_date <= CURDATE()
                         AND flash_sales.end_date >= CURDATE()
                         AND (flash_sales.start_time <= CURTIME() OR flash_sales.end_time >= CURTIME())
                         ORDER BY flash_sale_products.price $order LIMIT 1
                        ),
                        IF(discount_price > 0, discount_price, price)
                    ) $order
                ")->orderByDesc('id');
            });

        $total = $products->count();
        $products = $products->when($perPage && $page, function ($query) use ($perPage, $skip) {
            return $query->skip($skip)->take($perPage);
        })->get();

        return $this->json('products', [
            'total' => $total,
            'products' => ProductResource::collection($products),
            'filters' => [
                'sizes' => $sizes ? SizeResource::collection($sizes) : [],
                'colors' => $colors ? ColorResource::collection($colors) : [],
                'brands' => $brands ? BrandResource::collection($brands) : [],
                'min_price' => (int) intval($productMinPrice),
                'max_price' => (int) intval($productMaxPrice),
            ],
        ]);
    }

    private function getSearchLog($search)
    {
        if (!module_exists('report')) {
            return true;
        }
        \Modules\Report\App\Repositories\ProductSearchLogRepository::updateOrCreateByRequest($search);
    }

    /**
     * Show the product details.
     *
     * @param  datatype  $id  description
     * @return response
     */
    public function show(Request $request): JsonResponse
    {
        if ($request->slug) {
            $request->validate([
                'slug' => 'required|exists:products,slug',
            ]);
        } else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);
        }

        $preorderSetting = module_exists('PreOrder')
            ? \Modules\PreOrder\App\Models\PreOrderSetting::first()
            : null;

        $isPreorderMode = $preorderSetting
            ? app(\Modules\PreOrder\App\Policies\PreOrderPolicy::class)->canUsePreOrder(auth()->user() ?? new User(), $preorderSetting)
            : false;

        $product = ProductRepository::query()
            ->withoutGlobalScopes()
            ->when(!$isPreorderMode, fn($q) => $q->where('is_preorder', false))
            ->when($request->filled('product_id'), fn($q) => $q->where('id', $request->product_id))
            ->when($request->filled('slug'), fn($q) => $q->where('slug', $request->slug))
            ->isActive()
            ->first();

        if (!$product) {
            return $this->json('product not found');
        }
        ProductRepository::recentView($product->id);

        $relatedProducts = ProductRepository::query()->withoutGlobalScopes()
            ->when(!$isPreorderMode, fn($q) => $q->where('is_preorder', false))->whereHas('categories', function ($query) use ($product) {
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })->where('id', '!=', $product->id)
            ->isActive()
            ->inRandomOrder()
            ->limit(6)->get();

        $shop = $product->shop;

        $popularProducts = $shop->products()->isActive()->where('id', '!=', $product->id)->withCount('orders')->withAvg('reviews as average_rating', 'rating')->orderByDesc('average_rating')->orderByDesc('orders_count')->take(6)->get();
        $sellerOffers = ProductRepository::query()
                    ->with(['shop', 'flashSales'])
                    ->isActive()
                    ->where('id', '!=', $product->id)
                    ->where('shop_id','!=',$product->shop_id)
                    ->where('name', 'like', '%' . $product->name . '%')
                    ->orderByRaw('CASE WHEN discount_price > 0 THEN discount_price ELSE price END ASC')
                    ->limit(6)
                    ->get();

        return $this->json('product details', [
            'product' => ProductDetailsResource::make($product),
            'seller_offers' => ProductSellerOfferResource::collection($sellerOffers),
            'related_products' => ProductResource::collection($relatedProducts),
            'popular_products' => ProductResource::collection($popularProducts),
        ]);
    }

    /**
     * Add or remove favorite product for the user.
     *
     * @param  AddFavoriteRequest  $request  The request for adding a favorite.
     * @return json Response with favorite updated successfully
     */
    public function addFavorite(AddFavoriteRequest $request): JsonResponse
    {
        $product = ProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->findOrFail($request->product_id);

        auth()->user()?->customer->favorites()->toggle($product->id);

        return $this->json('favorite updated successfully', [
            'product' => ProductResource::make($product),
        ]);
    }

    /**
     * get list of favorite products.
     *
     * @return json Response
     */
    public function favoriteProducts(): JsonResponse
    {
        $products = auth()->user()->customer->favorites;

        return $this->json('favorite products', [
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Store a new review.
     *
     * @param  ReviewRequest  $request  The review request
     * @return json Response
     */
    public function storeReview(ReviewRequest $request): JsonResponse
    {
        $product = ProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->findOrFail($request->product_id);

        $hasReview = $product->reviews()->where('customer_id', auth()->user()->customer->id)->where('order_id', $request->order_id)->first();

        if ($hasReview) {
            return $this->json('review already exists', [
                'review' => ReviewResource::make($hasReview),
            ]);
        }

        $review = ReviewRepository::storeByRequest($request, $product);

        return $this->json('review added successfully', [
            'review' => ReviewResource::make($review),
        ]);
    }
}
