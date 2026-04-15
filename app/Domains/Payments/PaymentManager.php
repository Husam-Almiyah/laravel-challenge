<?php

namespace App\Domains\Payments;

use App\Domains\Payments\Contracts\PaymentGatewayInterface;
use App\Domains\Payments\Drivers\ApplePayDriver;
use App\Domains\Payments\Drivers\MadaDriver;
use App\Domains\Payments\Drivers\StripeDriver;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class PaymentManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('payments.default', 'mada');
    }

    /**
     * Create Mada driver.
     */
    public function createMadaDriver(): PaymentGatewayInterface
    {
        return new MadaDriver;
    }

    /**
     * Create Stripe driver.
     */
    public function createStripeDriver(): PaymentGatewayInterface
    {
        return new StripeDriver;
    }

    /**
     * Create Apple Pay driver.
     */
    public function createApplePayDriver(): PaymentGatewayInterface
    {
        return new ApplePayDriver;
    }

    /**
     * Resolve the driver by gateway name.
     */
    public function resolveByName(string $name): PaymentGatewayInterface
    {
        try {
            // Str::studly('mada') -> 'Mada'
            // However, the standard implementation of driver() expects driver names to match creator names if they are custom.
            // If the name is 'mada', it expects createMadaDriver. Laravel's driver() translates 'mada' to createMadaDriver().
            // So we can just call driver() and let Manager cache and resolve it.
            return $this->driver($name);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Driver [$name] not supported.");
        }
    }
}
