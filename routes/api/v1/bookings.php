<?php

use App\Http\Controllers\Api\V1\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booking Management
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('bookings', BookingController::class)->except(['update']);
});
