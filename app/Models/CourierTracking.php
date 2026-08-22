<?php

namespace App\Models;

use App\Library\SteadFast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PreOrder\App\Models\PreOrder;

class CourierTracking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status_history' => 'array',
    ];

    /**
     * Get the order associated with the courier tracking.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the pre-order associated with the courier tracking.
     */
    public function preOrder(): BelongsTo
    {
        return $this->belongsTo(PreOrder::class);
    }

    /**
     * Add status to history
     */
    public function addStatusHistory($status, $note = null)
    {
        $history = $this->status_history ?? [];
        $history[] = [
            'status' => $status,
            'note' => $note,
            'timestamp' => now()->toDateTimeString(),
        ];

        $this->status_history = $history;
        $this->save();
    }

    /**
     * Get status description
     */
    public function getStatusDescriptionAttribute()
    {
        $descriptions = (new SteadFast)->getDeliveryStatusDescriptions();

        return $descriptions[$this->status] ?? 'Unknown status';
    }

    /**
     * Scope for active trackings
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }

    /**
     * Scope for delivered trackings
     */
    public function scopeDelivered($query)
    {
        return $query->whereIn('status', ['delivered', 'partial_delivered']);
    }
}
