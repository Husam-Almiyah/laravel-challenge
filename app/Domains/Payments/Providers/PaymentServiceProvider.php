<?php

namespace App\Domains\Payments\Providers;

use App\Domains\Payments\Events\PaymentFailed;
use App\Domains\Payments\Events\PaymentInitiated;
use App\Domains\Payments\Events\PaymentSucceeded;
use App\Domains\Payments\Events\WebhookReceived;
use App\Domains\Payments\Listeners\LogPaymentFailed;
use App\Domains\Payments\Listeners\LogPaymentInitiated;
use App\Domains\Payments\Listeners\LogPaymentSucceeded;
use App\Domains\Payments\Listeners\LogWebhookReceived;
use App\Domains\Payments\PaymentManager;
use App\Domains\Payments\Services\GatewayAvailabilityResolver;
use Illuminate\Support\Facades\Event;
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
        Event::listen(
            PaymentInitiated::class,
            LogPaymentInitiated::class
        );

        Event::listen(
            PaymentSucceeded::class,
            LogPaymentSucceeded::class
        );

        Event::listen(
            PaymentFailed::class,
            LogPaymentFailed::class
        );

        Event::listen(
            WebhookReceived::class,
            LogWebhookReceived::class
        );
    }
}
