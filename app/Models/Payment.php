<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the orders for the Payment
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payments', 'payment_id', 'order_id');
    }
    /**
     * Get all of the Preorders for the Payment
     */
    public function preOrders()
    {
        if (! module_exists('preOrder')) {
            return null;
        }
        return $this->belongsToMany(\Modules\PreOrder\App\Models\PreOrder::class, 'pre_order_payments', 'payment_id', 'order_id');
    }
}
