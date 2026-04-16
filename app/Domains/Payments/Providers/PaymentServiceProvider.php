<?php

namespace App\Domains\Payments\Providers;

use App\Domains\Payments\PaymentManager;
use App\Domains\Payments\Services\GatewayAvailabilityResolver;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager($app);
        });

        $this->app->singleton(GatewayAvailabilityResolver::class, function ($app) {
            return new GatewayAvailabilityResolver;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Event listeners are auto-discovered via Laravel's event discovery
        // Listeners in app/Domains/Payments/Listeners follow the naming convention:
        // PaymentInitiated -> LogPaymentInitiated, FulfillTransaction
        // PaymentSucceeded -> LogPaymentSucceeded, FulfillTransaction
        // PaymentFailed -> LogPaymentFailed
        // PaymentCompleted -> LogPaymentCompleted
        // WebhookReceived -> LogWebhookReceived
    }
}
