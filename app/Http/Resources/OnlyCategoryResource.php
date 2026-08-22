<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlyCategoryResource extends JsonResource
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
            'id' => $this->id ?? null,
            'slug' => $this->slug ?? null,
            'name' => ($isAr && !empty($this->name_ar)) ? $this->name_ar : ($this->name ?? null),
            'thumbnail' => $this->thumbnail ?? null,
        ];
    }
}
