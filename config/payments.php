<?php

return [

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

];
