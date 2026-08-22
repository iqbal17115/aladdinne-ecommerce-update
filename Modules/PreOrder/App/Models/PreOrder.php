<?php

namespace Modules\PreOrder\App\Models;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\PreOrder\App\Enums\PreOrderStatus;
use Modules\PreOrder\App\Enums\PrePaymentStatus;
use Modules\PreOrder\App\Enums\RefundStatus;
// use Modules\PreOrder\Database\Factories\PreOrderFactory;

class PreOrder extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'order_status' => PreOrderStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_status' => PrePaymentStatus::class,
        'refund_status' => RefundStatus::class,
        'refunded_at' => 'datetime',
        'is_refundable' => 'boolean',
    ];
    public function preOrderItem()
    {
        return $this->hasOne(PreOrderItem::class, 'pre_order_id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
    public function driverPreOrder(): BelongsTo
    {
        return $this->belongsTo(DriverPreOrder::class, 'id', 'pre_order_id');
    }
    public function isPaid(): bool
    {
        return $this->payment_status->value === PrePaymentStatus::PAID->value;
    }
    public function hasActiveRefund(): bool
    {
        return in_array(optional($this->refund_status)->value, [
            RefundStatus::REQUESTED->value,
            RefundStatus::APPROVED->value,
            RefundStatus::REFUNDED->value,
        ], true);
    }
    public function paidTotal(): float
    {
        return (float) ($this->isPaid() ? $this->payable_amount : $this->paid_amount);
    }
    public function scopeDelivered($query)
    {
        return $query->where('order_status', 'Delivered');
    }
    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('pre_orders.order_code', 'like', "%{$search}%");
            });
        }
    }
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'pre_order_payments', 'order_id', 'payment_id');
    }
}
