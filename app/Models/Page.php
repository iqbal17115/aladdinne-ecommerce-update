<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function icon(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $icon = $attributes['icon'] ?? null;

                if ($icon && Storage::exists($icon)) {
                    return Storage::url($icon);
                }

                return null;
            }
        );
    }
}
