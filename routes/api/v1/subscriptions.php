<?php

use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Subscription Management
|--------------------------------------------------------------------------
*/
Route::prefix('subscriptions')->group(function () {
    // Public plans browsing
    Route::get('plans', [SubscriptionController::class, 'plans']);

    // Protected subscription actions
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('trial', [SubscriptionController::class, 'activateTrial']);
        Route::get('my', [SubscriptionController::class, 'mySubscription']);
    });
});
