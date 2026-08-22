<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeIsActive(Builder $builder)
    {
        return $builder->where('is_active', true);
    }

    public function getAddresses()
    {
        return $this->hasMany(Address::class, 'area_id');
    }
}
