<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->load(['reviews', 'orders', 'sizes', 'colors', 'unit', 'brand', 'shop', 'flashSales']);

        $lang = request()->header('accept-language') ?? 'en';

        $favorite = false;
        $user = Auth::guard('api')->user();

        if ($user && $user->customer) {
            $favorite = $user->customer->favorites()->where('product_id', $this->id)->exists();
        }

        $discountPercentage = $this->getDiscountPercentage($this->price, $this->discount_price);
        $totalSold = $this->orders->sum('pivot.quantity');

        $flashSale = $this->flashSales?->first();
        $flashSaleProduct = null;
        $quantity = null;

        if ($flashSale) {
            $flashSaleProduct = $flashSale?->products()->where('id', $this->id)->first();

            $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

            if ($quantity == 0) {
                $quantity = null;
                $flashSaleProduct = null;
            } else {
                $discountPercentage = $flashSale?->pivot->discount;
            }
        }

        $price = $this->price;
        $discountPrice = $flashSaleProduct ? $flashSaleProduct->pivot->price : $this->discount_price;

        $isAr = $lang !== 'en';
        $name = ($isAr && !empty($this->name_ar)) ? $this->name_ar : $this->name;
        $shortDescription = ($isAr && !empty($this->short_description_ar)) ? $this->short_description_ar : $this->short_description;
        $brandName = ($isAr && !empty($this->brand?->name_ar)) ? $this->brand->name_ar : $this->brand?->name;
        module_exists('PreOrder') ? $isPreorder = (bool) $this->is_preorder : $isPreorder = false;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'is_digital' => (bool) $this->is_digital,
            'name' => $name,
            'short_description' => $shortDescription,
            'thumbnail' => $this->thumbnail,
            'price' => (float) number_format($price, 2, '.', ''),
            'discount_price' => (float) number_format($discountPrice, 2, '.', ''),
            'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
            'discount_type' => $this->discount_type,
            'discount'   => (float) number_format($this->discount, 2, '.', ''),
            'rating' => (float) $this->averageRating ?? 0.0,
            'total_reviews' => (string) Number::abbreviate($this->reviews?->count(), maxPrecision: 2),
            'total_sold' => (string) number_format($totalSold, 0, '.', ','),
            'quantity' => (int) ($flashSaleProduct ? $quantity : $this->quantity),
            'is_favorite' => (bool) $favorite,
            'sizes' => SizeResource::collection($this->sizes),
            'colors' => ColorResource::collection($this->colors),
            'unit' => $this->unit ? UnitResource::make($this->unit) : null,
            'brand' => $brandName,
            'is_preorder' => $isPreorder,
            'is_prepay' => (bool) $this->is_prepay ?? false,
            'shop' => ProductShopResource::make($this->shop),
        ];
    }
}
