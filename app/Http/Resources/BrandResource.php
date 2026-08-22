<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAr = (request()->header('accept-language') ?? 'en') !== 'en';

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => ($isAr && !empty($this->name_ar)) ? $this->name_ar : $this->name,
            'thumbnail' => $this->thumbnail,
            'product_count' => $this->whenCounted('products'),
        ];
    }
}
