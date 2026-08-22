<?php

namespace Modules\PreOrder\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $rate = $this->currency_rate ?? 1;
        $currencyAmount = $this->prepay_amount * $rate;
        return [
            'is_available' => (bool) $this->is_available,
            'is_prepay' => (bool) $this->is_prepay,
            'prepay_amount' => $currencyAmount,
            'expected_delivery_date' => $this->expected_delivery_date ?? '',
            'min_order_quantity' => $this->min_order_quantity ?? 1,
            'preorder_quantity_limit' => $this->preorder_quantity_limit ?? 1,
            'is_refund' => (bool) $this->is_refund,
            'preorder_notice' => $this->preorder_notice ?? '',
            'refund_policy' => $this->refund_policy ?? '',
            'preOrder_policy' => $this->preOrder_policy ?? '',
        ];
    }
}
