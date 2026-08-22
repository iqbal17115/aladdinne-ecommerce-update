<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SupportItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function iconUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk('public')->exists($this->icon)
                ? Storage::url($this->icon)
                : asset($this->icon),
        );
    }
}
