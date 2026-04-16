<?php

use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments')->group(function () {
    Route::get('gateways', [PaymentController::class, 'index']);
    Route::post('initiate', [PaymentController::class, 'store'])->middleware('throttle:10,1');

    // Webhook endpoint for gateway notifications
    Route::post('webhooks/{gatewayName}', [WebhookController::class, 'handle'])->middleware('throttle:60,1')
        ->name('payments.webhook');
});
