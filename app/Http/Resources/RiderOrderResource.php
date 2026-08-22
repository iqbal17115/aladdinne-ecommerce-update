<?php

namespace App\Http\Resources;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isNormalOrder = $this->relationLoaded('order') && $this->order;
        $order = $isNormalOrder ? $this->order : $this->preOrder;
        if (!$order) {
            return [];
        }
        return [
            'id' => $order->id,
            'order_type' => $isNormalOrder ? 'order' : 'pre-order',
            'order_code' => '#' . ($order->prefix ?? '') . ($order->order_code ?? ''),
            'amount' => (float) ($order->payable_amount ?? 0),
            'order_status' => $order->order_status?->value,
            'payment_status' => $order->payment_status?->value,
            'payment_method' => $order->payment_method?->value == PaymentMethod::CASH->value ? 'Cash' : 'Online',
            'user' => [
                'name'  => $order->customer?->user?->name,
                'phone' => $order->customer?->user?->phone,
                'address' => $order->address ? AddressResource::make($order->address) : [],
            ],
            'shop' => [
                'name'      => $order->shop?->name,
                'phone'     => $order->shop?->user?->phone,
                'address'   => $order->shop?->address,
                'latitude'  => $order->shop?->latitude,
                'longitude' => $order->shop?->longitude
            ]
        ];
    }
}
