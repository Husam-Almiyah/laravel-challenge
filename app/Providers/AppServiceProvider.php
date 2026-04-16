<?php

namespace App\Providers;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\Cart;
use App\Policies\BookingPolicy;
use App\Policies\CartPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // Register policies
        Gate::policy(Cart::class, CartPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
