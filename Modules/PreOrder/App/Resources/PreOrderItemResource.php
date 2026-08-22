<?php

namespace Modules\PreOrder\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreOrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $rate= $this->currency_rate ?? 1;
        $price= $this->price * $rate;
        $discount= $this->discount * $rate;
        $productPrice= $this->product_price * $rate;
        return [
            "id"=> $this->id,
            "product_id"=>$this->product_id,
            "product_slug"=>$this->product?->slug ?? '',
            'product_name' => $this->product_name,
            'product_price'=> $productPrice,
            'discount'   =>$discount,
            'quantity'   => $this->quantity,
            'price'      => $price,
            'total_price'=> $price * $this->quantity,

        ];
    }
}
