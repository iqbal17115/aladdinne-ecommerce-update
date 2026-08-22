<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, HasUniqueSlug;

    protected $guarded = ['id'];

    public function translations(): HasMany
    {
        return $this->hasMany(TranslateUtility::class);
    }

    /**
     * Retrieves the products associated with this instance.
     *
     * @return BelongsToMany The products associated with this instance.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id');
    }

    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'category_subcategories')->where('is_active', 1);
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_categories');
    }

    /**
     * Scopes a query to only include active records.
     *
     * @param  mixed  $query  The query parameter.
     * @return mixed The return value.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_by')->orderBy('id');
    }

    /**
     * Retrieves the associated media for this model.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Generates a thumbnail attribute for the media.
     *
     * @return Attribute The generated thumbnail attribute.
     */
    public function thumbnail(): Attribute
    {
        $thumbnail = asset('default/default.jpg');
        if ($this->category_thumbnail && Storage::exists($this->category_thumbnail)) {
            $thumbnail = Storage::url($this->category_thumbnail);
        } elseif ($this->media && Storage::exists($this->media->src)) {
            $thumbnail = Storage::url($this->media->src);
        }

        return Attribute::make(
            get: fn () => $thumbnail
        );
    }
    public function icon(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $icon = $attributes['icon'] ?? null;

                if ($icon && Storage::exists($icon)) {
                    return Storage::url($icon);
                }

                return asset('default/default.jpg');
            }
        );
    }

    public function banner(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $banner = $attributes['banner'] ?? null;

                if ($banner && Storage::exists($banner)) {
                    return Storage::url($banner);
                }

                return null;
                // return asset('default/default.jpg');
            }
        );
    }
}
