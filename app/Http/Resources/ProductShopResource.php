<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastOnline = $this->last_online >= now() ? true : false;

        $isAr = (request()->header('accept-language') ?? 'en') !== 'en';

        return [
            'id' => $this->id,
            'name' => ($isAr && !empty($this->name_ar)) ? $this->name_ar : $this->name,
            'address' => $this->address,
            'logo' => $this->logo,
            'rating' => (float) ($this->averageRating > 0) ? $this->averageRating : 5.0,
            'estimated_delivery_time' => (string) ($this->estimated_delivery_time ?? '2-3 days'),
            'delivery_charge' => (float) getDeliveryCharge(1),
            'last_online' => $lastOnline
        ];
    }
}
