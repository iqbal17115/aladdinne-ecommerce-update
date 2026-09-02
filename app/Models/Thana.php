<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Thana extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeIsActive(Builder $builder)
    {
        return $builder->where('is_active', true);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
