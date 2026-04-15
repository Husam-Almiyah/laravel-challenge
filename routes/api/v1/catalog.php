<?php

use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('catalog')->group(function () {
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{slug}', [ServiceController::class, 'show']);
    Route::get('packages', [PackageController::class, 'index']);
    Route::get('packages/{slug}', [PackageController::class, 'show']);
});
