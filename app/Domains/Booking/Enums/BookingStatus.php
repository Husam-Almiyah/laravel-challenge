<?php

namespace App\Domains\Booking\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    /**
     * Get all active statuses that can be cancelled.
     */
    public static function cancellableStatuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
        ];
    }

    /**
     * Check if the booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, self::cancellableStatuses(), true);
    }
}
