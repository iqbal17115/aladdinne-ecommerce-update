<?php

namespace Modules\PreOrder\App\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\ShopResource;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Enums\PrePaymentStatus;
use Modules\PreOrder\App\Resources\PreOrderItemResource;

class PreOrderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $rate= $this->currency_rate ?? 1;
        $currencyAmount= $this->payable_amount * $rate;

        // Amount still due for online payment. Pending = the amount set at order
        // time (the prepay amount for prepay products, otherwise the full
        // payable); Prepaid = the remaining balance after the prepayment.
        $isPending = $this->payment_status->value === PrePaymentStatus::PENDING->value;
        $isPrepaid = $this->payment_status->value === PrePaymentStatus::PREPAID->value;
        $notCancelled = ! in_array($this->order_status->value, [PreOrderStatus::CANCELLED->value, PreOrderStatus::REFUNDED->value], true);
        $attachedPayment = $this->payments->first();
        if ($isPending) {
            $dueBase = $attachedPayment ? $attachedPayment->amount : ($this->payable_amount - $this->paid_amount);
        } elseif ($isPrepaid) {
            $dueBase = $this->payable_amount - $this->paid_amount;
        } else {
            $dueBase = 0;
        }
        $dueAmount = (float) number_format(max($dueBase, 0) * $rate, 2, '.', '');

        // Refund: a delivered, paid order with no active refund can be requested.
        $isDelivered = $this->order_status->value === PreOrderStatus::DELIVERED->value;
        $paidTotal = $this->isPaid() ? $this->payable_amount : $this->paid_amount;
        $isRefundRequestable = $this->is_refundable && $isDelivered && $paidTotal > 0 && ! $this->hasActiveRefund();

        // Cancel: the customer can cancel only while the order is still in an
        // early stage (before shipping).
        $isCancellable = in_array($this->order_status->value, [
            PreOrderStatus::REQUESTED->value,
            PreOrderStatus::ACCEPTED->value,
            PreOrderStatus::PREPAYMENT_REQUESTED->value,
            PreOrderStatus::PREPAYMENT_CONFIRMED->value,
        ], true);

        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'order_status' => $this->order_status->label(),
            'currency_symbol'=>$this->currency_symbol ?? '$',
            'quantity' => (int) $this->preOrderItem?->quantity ?? 0,
            'amount' => (float) number_format($currencyAmount, 2, '.', ''),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status->value,
            'is_online_payable' => ($isPending || $isPrepaid) && $notCancelled && $dueAmount > 0,
            'due_amount' => $dueAmount,
            'refund_status' => $this->refund_status?->value,
            'refund_status_label' => $this->refund_status?->label(),
            'refund_reason' => $this->refund_reason,
            'refund_note' => $this->refund_note,
            'refund_amount' => (float) number_format(($this->refund_amount ?? 0) * $rate, 2, '.', ''),
            'is_refundable' => (bool) $this->is_refundable,
            'is_refund_requestable' => $isRefundRequestable,
            'is_cancellable' => $isCancellable,
            'created_at' => $this->created_at,
            'placed_at' => $this->created_at->format('d M, Y h:i A'),
            'address' => $this->address ? AddressResource::make($this->address) : [],
            'shop' => $this->shop ? ShopResource::make($this->shop) : [],
            'invoice_url' => route('shop.preOrder.invoice', $this->id),
            'payment_receipt_url' => route('shop.preOrder.paymentInvoice', $this->id),
            'item'     =>$this->preOrderItem ? PreOrderItemResource::make($this->preOrderItem) : []
        ];
    }
}
