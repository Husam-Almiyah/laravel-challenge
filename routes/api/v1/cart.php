<?php

use App\Http\Controllers\Api\V1\CartController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('items', [CartController::class, 'store']);
        Route::put('items/{itemId}', [CartController::class, 'update']);
        Route::delete('items/{itemId}', [CartController::class, 'destroy']);
        Route::delete('clear', [CartController::class, 'clear']);
    });
});
