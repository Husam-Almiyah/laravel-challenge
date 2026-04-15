<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Auth Routes (Livewire SFCs)
Route::livewire('/login', 'auth.login')->name('login')->middleware('guest');
Route::livewire('/register', 'auth.register')->name('register')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Client Dashboard
    Route::get('/dashboard', function () {
        return view('client.dashboard');
    })->name('dashboard');

    // Client Features
    Route::livewire('/catalog', 'client.catalog.index')->name('catalog');
    Route::livewire('/cart', 'client.cart.index')->name('cart');
    Route::livewire('/checkout', 'client.checkout.process')->name('checkout');

    // Admin Dashboard
    Route::middleware(['can:admin'])->group(function () {
        Route::get('/admin', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });
});
