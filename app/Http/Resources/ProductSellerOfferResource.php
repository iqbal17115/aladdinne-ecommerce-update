<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSellerOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['shop', 'flashSales']);
        $lang = request()->header('accept-language') ?? 'en';

        $flashSale = $this->flashSales?->first();
        $flashSaleProduct = $flashSale?->products()->where('id', $this->id)->first();
        $finalPrice = $flashSaleProduct ? $flashSaleProduct->pivot?->price : $this->discount_price;
        $price = $this->price;
        $translation = $this->translations()?->where('lang', $lang)->first();
        $name = $translation?->name ?? $this->name;

        if (! $finalPrice || $finalPrice <= 0) {
            $finalPrice = $price;
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $name,
            'thumbnail' => $this->thumbnail,
            'price' => (float) number_format($price, 2, '.', ''),
            'discount_price' => (float) number_format($finalPrice, 2, '.', ''),
            'shop' => ProductShopResource::make($this->shop),
        ];
    }
}
