<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookingItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'booking_id',
        'itemable_id',
        'itemable_type',
        'name',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the parent booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the item entity (Service or Package).
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
