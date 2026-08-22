<?php

namespace Modules\Purchase\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseListResource extends JsonResource
{
    /**
     * Transform the resource into an array (seller purchase list row).
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_code' => $this->purchase_code,
            'name' => $this->name,
            'supplier' => $this->supplier ? [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
                'phone' => $this->supplier->phone,
            ] : null,
            'total_amount' => (float) number_format($this->total_amount ?? 0, 2, '.', ''),
            'paid_amount' => (float) number_format($this->paid_amount ?? 0, 2, '.', ''),
            'total_product' => (int) $this->total_product,
            'is_received' => (bool) $this->is_received,
            'receive_date' => $this->receive_date,
            'created_at' => $this->created_at,
        ];
    }
}
