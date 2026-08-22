<?php

namespace Modules\PreOrder\App\Resources;

use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\PreOrder\App\Repositories\PreOrderRepository;

class SellerPreOrderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array (seller pre-order details).
     */
    public function toArray(Request $request): array
    {
        $rate = $this->currency_rate ?? 1;
        $rider = $this->driverPreOrder?->driver;

        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'order_status' => $this->order_status->label(),
            'order_status_value' => $this->order_status->value,
            'payment_status' => $this->payment_status->value,
            'payment_method' => $this->payment_method,
            'currency_symbol' => $this->currency_symbol ?? '$',
            'quantity' => (int) ($this->preOrderItem?->quantity ?? 0),
            'total_amount' => (float) number_format($this->total_amount * $rate, 2, '.', ''),
            'delivery_charge' => (float) number_format($this->delivery_charge * $rate, 2, '.', ''),
            'amount' => (float) number_format($this->payable_amount * $rate, 2, '.', ''),
            'paid_amount' => (float) number_format($this->paid_amount * $rate, 2, '.', ''),
            'admin_commission' => (float) number_format(($this->admin_commission ?? 0) * $rate, 2, '.', ''),
            'is_refundable' => (bool) $this->is_refundable,
            'refund_status' => $this->refund_status?->value,
            'refund_status_label' => $this->refund_status?->label(),
            'refund_reason' => $this->refund_reason,
            'refund_amount' => (float) number_format(($this->refund_amount ?? 0) * $rate, 2, '.', ''),
            'customer_note' => $this->customer_note,
            'purchase_id' => $this->purchase_id,
            'is_purchasable' => module_exists('Purchase')
                && ! $this->purchase_id
                && ! in_array($this->order_status->value, [
                    \Modules\PreOrder\App\Enums\PreOrderStatus::CANCELLED->value,
                    \Modules\PreOrder\App\Enums\PreOrderStatus::DELIVERED->value,
                    \Modules\PreOrder\App\Enums\PreOrderStatus::REFUNDED->value,
                ], true),
            'created_at' => $this->created_at,
            'placed_at' => $this->created_at->format('d M, Y h:i A'),
            'customer' => [
                'name' => $this->customer?->user?->fullName,
                'phone' => $this->customer?->user?->phone,
                'thumbnail' => $this->customer?->user?->thumbnail,
            ],
            'address' => $this->address ? AddressResource::make($this->address) : null,
            'rider' => $rider ? [
                'id' => $rider->id,
                'name' => $rider->user?->fullName,
                'phone' => $rider->user?->phone,
                'thumbnail' => $rider->user?->thumbnail,
            ] : null,
            'item' => $this->preOrderItem ? PreOrderItemResource::make($this->preOrderItem) : null,
            'allowed_statuses' => collect(PreOrderRepository::allowedNextStatuses($this->order_status))
                ->map(fn ($status) => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])->values(),
            'invoice_url' => route('shop.preOrder.invoice', $this->id),
            'payment_receipt_url' => route('shop.preOrder.paymentInvoice', $this->id),
        ];
    }
}
