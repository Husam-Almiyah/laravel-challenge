<?php

namespace App\Domains\Booking\Models;

use App\Domains\Account\Models\Address;
use App\Domains\Booking\Enums\BookingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'address_id',
        'scheduled_at',
        'total_amount',
        'status',
        'metadata',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'metadata' => 'array',
        'status' => BookingStatus::class,
    ];

    /**
     * Get the user who made this booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address where the service is scheduled.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the items included in this booking.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }
}
