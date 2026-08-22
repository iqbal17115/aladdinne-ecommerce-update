<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductThumbnail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'thumbnail',
        'media_id',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
