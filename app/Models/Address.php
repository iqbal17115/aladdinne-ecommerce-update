<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the customer that owns the address.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all of the orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // public function area(): BelongsTo
    // {
    //     return $this->belongsTo(Area::class, 'area_id');
    // }

    public function getArea(){
        return $this->hasOne(Area::class, 'id', 'area_id');
    }

    public function deliveryAmount()
    {
        return $this->getArea->delivery_amount ?? 0;
    }
}
