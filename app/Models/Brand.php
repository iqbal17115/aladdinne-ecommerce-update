<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory, HasUniqueSlug;

    protected $guarded = ['id'];

    /**
     * Get the shop from the brand.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TranslateUtility::class);
    }

    /**
     * Get the products from the brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: function () {
                $thumbnail = $this->getRawOriginal('brand_thumbnail');

                if ($thumbnail && Storage::exists($thumbnail)) {
                    return Storage::url($thumbnail);
                }

                return asset('default/default.jpg');
            }
        );
    }

    /**
     * Scope a query to only include active brands.
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', 1);
    }
}
