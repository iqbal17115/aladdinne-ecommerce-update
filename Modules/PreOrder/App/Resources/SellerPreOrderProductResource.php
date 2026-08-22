<?php

namespace Modules\PreOrder\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerPreOrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array (seller pre-order product list row).
     */
    public function toArray(Request $request): array
    {
        $isEcommerce = config('app.project_key') === 'ReadyEcommerce';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'thumbnail' => $this->thumbnail,
            'price' => (float) $this->price,
            'discount_price' => (float) $this->discount_price,
            'quantity' => (int) $this->quantity,
            'unit' => $isEcommerce ? $this->unit?->name : $this->unit,
            'brand' => $this->brand?->name,
            'is_active' => (bool) $this->is_active,
            'is_available' => (bool) $this->is_available,
            'is_prepay' => (bool) $this->is_prepay,
            'prepay_amount' => (float) $this->prepay_amount,
            'expected_delivery_date' => $this->expected_delivery_date ?? '',
        ];
    }
}
