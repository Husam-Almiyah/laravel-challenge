<?php

namespace App\Domains\Payments\Enums;

/**
 * Payment method types available in the system.
 */
enum PaymentMethodEnum: string
{
    case Mada = 'mada';
    case Stripe = 'stripe';
    case ApplePay = 'apple_pay';

    /**
     * Get the display label for the payment method.
     */
    public function label(): string
    {
        return match ($this) {
            self::Mada => 'Mada',
            self::Stripe => 'Stripe',
            self::ApplePay => 'Apple Pay',
        };
    }

    /**
     * Get the driver class name for this payment method.
     */
    public function driver(): string
    {
        return match ($this) {
            self::Mada => 'MadaDriver',
            self::Stripe => 'StripeDriver',
            self::ApplePay => 'ApplePayDriver',
        };
    }

    /**
     * Check if this payment method requires 3D Secure redirect.
     */
    public function requiresRedirect(): bool
    {
        return match ($this) {
            self::Stripe => true,
            default => false,
        };
    }

    /**
     * Check if this payment method supports webhooks.
     */
    public function supportsWebhooks(): bool
    {
        return match ($this) {
            self::Stripe, self::Mada => true,
            self::ApplePay => false,
        };
    }

    /**
     * Get all payment methods as key-value array.
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }

        return $array;
    }
}
