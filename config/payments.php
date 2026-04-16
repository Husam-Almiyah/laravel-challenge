<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mock Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, payment gateway drivers use simulated responses instead
    | of real API calls. This should ALWAYS be false in production.
    |
    | Mock behaviors per driver:
    | - Mada: Amount of 404.00 always fails; all others succeed
    | - Stripe: Amounts > 500 trigger 3DS redirect; others succeed
    | - Apple Pay: Always succeeds immediately
    |
    */

    'mock_mode' => env('PAYMENT_MOCK_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used by the
    | application. You may set this to any of the supported gateways.
    |
    | Supported: "mada", "stripe", "apple_pay"
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'mada'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook signature verification and security settings.
    |
    */

    'webhooks' => [
        'require_signature' => env('PAYMENT_WEBHOOK_REQUIRE_SIGNATURE', false),
    ],

    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET', ''),

];
