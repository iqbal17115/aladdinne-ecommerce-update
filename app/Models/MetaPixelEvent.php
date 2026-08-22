<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A row per commerce event (ViewContent/AddToCart/InitiateCheckout/Purchase)
 * mirrored to the Meta Conversions API, kept for the admin panel's tracking
 * log. PageView is intentionally not logged here — it would dwarf the table
 * with little insight. Rows are pruned by app\Console\Commands\PruneMetaPixelEvents.
 */
class MetaPixelEvent extends Model
{
    /**
     * PageView is deliberately excluded: it fires on every page load and would
     * dwarf the admin log with no commerce insight.
     */
    public const LOGGED_EVENTS = ['ViewContent', 'AddToCart', 'InitiateCheckout', 'Purchase'];

    protected $guarded = ['id'];

    protected $casts = [
        'product_ids' => 'array',
        'value' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
