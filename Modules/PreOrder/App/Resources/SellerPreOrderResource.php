<?php

namespace Modules\PreOrder\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerPreOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array (seller pre-order list row).
     */
    public function toArray(Request $request): array
    {
        $rate = $this->currency_rate ?? 1;

        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'order_status' => $this->order_status->label(),
            'order_status_value' => $this->order_status->value,
            'payment_status' => $this->payment_status->value,
            'payment_method' => $this->payment_method,
            'currency_symbol' => $this->currency_symbol ?? '$',
            'quantity' => (int) ($this->preOrderItem?->quantity ?? 0),
            'amount' => (float) number_format($this->payable_amount * $rate, 2, '.', ''),
            'customer' => [
                'name' => $this->customer?->user?->fullName,
                'phone' => $this->customer?->user?->phone,
                'thumbnail' => $this->customer?->user?->thumbnail,
            ],
            'created_at' => $this->created_at,
            'placed_at' => $this->created_at->format('d M, Y h:i A'),
        ];
    }
}
