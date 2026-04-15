<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1 (V1)
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Dynamically include all route files in api/v1 directory
    foreach (glob(__DIR__.'/api/v1/*.php') as $file) {
        require $file;
    }
});
