<?php

namespace App\Domains\Payments\Enums;

/**
 * Transaction types for different payment scenarios.
 */
enum TransactionTypeEnum: string
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Capture = 'capture';
    case Authorization = 'authorization';

    /**
     * Get the display label for the transaction type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Payment => 'Payment',
            self::Refund => 'Refund',
            self::Capture => 'Capture',
            self::Authorization => 'Authorization',
        };
    }

    /**
     * Check if this transaction type moves money to merchant.
     */
    public function isDebit(): bool
    {
        return match ($this) {
            self::Payment, self::Capture, self::Authorization => true,
            self::Refund => false,
        };
    }
}
