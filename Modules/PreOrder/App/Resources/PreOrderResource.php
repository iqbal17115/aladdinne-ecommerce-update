<?php

namespace Modules\PreOrder\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $rate= $this->currency_rate ?? 1;
        $currencyAmount= $this->payable_amount * $rate;

        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'order_status' => $this->order_status->label(),
            'currency_symbol'=>$this->currency_symbol ?? '$',
            'quantity' => (int) $this->preOrderItem?->quantity ?? 0,
            'amount' => (float) number_format($currencyAmount, 2, '.', ''),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status->value,
            'created_at' => $this->created_at,
            'placed_at' => $this->created_at->format('d M, Y h:i A')
        ];
    }
}
