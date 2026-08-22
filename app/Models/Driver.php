<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Driver extends Model
{
    use HasFactory ,SoftDeletes;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, (new DriverOrder)->getTable())->withPivot('is_completed', 'assign_for', 'is_accept', 'cash_collect', 'driver_id', 'order_id', 'created_at', 'updated_at');
    }

    public function driverOrders(): HasMany
    {
        return $this->hasMany(DriverOrder::class, 'driver_id');
    }
    public function driverPreOrders(): ?HasMany
    {
        if (module_exists('PreOrder')) {
            return $this->hasMany(\Modules\PreOrder\App\Models\DriverPreOrder::class, 'driver_id');
        }
        return null;
    }

    public function incompleteOrders(): int
    {
        $orderCount = $this->orders()
            ->whereHas('driverOrder', function ($query) {
                $query->where('is_completed', 0);
            })
            ->count();
        $preOrderCount = 0;
        if (module_exists('PreOrder') && $this->driverPreOrders()) {
            $preOrderCount = $this->driverPreOrders()
                ->where('is_completed', 0)
                ->count();
        }
        return $orderCount + $preOrderCount;
    }

    // --------- scope ---------------
    public function scopeIsApprove(Builder $builder, $isApprove = true): Builder
    {
        return $builder->where('is_approve', $isApprove);
    }

    public function driverLocation()
    {
        return $this->hasOne(DriverLocation::class,'driver_id');
    }
}
