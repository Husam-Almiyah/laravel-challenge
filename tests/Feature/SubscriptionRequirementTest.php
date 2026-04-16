<?php

use App\Domains\Account\Models\Address;
use App\Domains\Booking\Models\Cart;
use App\Domains\Booking\Models\CartItem;
use App\Domains\Booking\Services\BookingService;
use App\Domains\Catalog\Models\Service;
use App\Domains\Subscriptions\Enums\SubscriptionStatus;
use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user without active subscription cannot book services', function () {
    $user = User::factory()->create();
    $service = Service::factory()->create(['price' => 100]);
    $cart = Cart::create(['user_id' => $user->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'itemable_id' => $service->id,
        'itemable_type' => Service::class,
        'name' => $service->name,
        'price' => $service->price,
        'quantity' => 1,
    ]);

    $bookingService = new BookingService;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('An active subscription is required to book maintenance services.');

    $bookingService->createFromCart($user, [
        'address_id' => 'some-id',
        'scheduled_at' => now()->addDay(),
        'notes' => 'Test booking',
    ]);
});

test('user with active subscription can book services', function () {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();

    // Create active subscription
    Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'status' => SubscriptionStatus::ACTIVE,
    ]);

    $city = City::factory()->create(['name' => 'Riyadh']);
    $address = Address::create([
        'user_id' => $user->id,
        'city_id' => $city->id,
        'address_details' => '123 Test St',
        'district' => 'Testing',
    ]);

    $service = Service::factory()->create(['price' => 100]);
    $cart = Cart::create(['user_id' => $user->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'itemable_id' => $service->id,
        'itemable_type' => Service::class,
        'name' => $service->name,
        'price' => $service->price,
        'quantity' => 1,
    ]);

    $bookingService = new BookingService;

    $booking = $bookingService->createFromCart($user, [
        'address_id' => $address->id,
        'scheduled_at' => now()->addDay(),
        'notes' => 'Test booking',
    ]);

    expect($booking)->not->toBeNull()
        ->and($booking->user_id)->toBe($user->id);
});

test('user with expired subscription cannot book services', function () {
    $user = User::factory()->create();
    $plan = SubscriptionPlan::factory()->create();

    // Create expired subscription
    Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'starts_at' => now()->addDays(-10),
        'ends_at' => now()->addDays(-1),
        'status' => SubscriptionStatus::ACTIVE,
    ]);

    $service = Service::factory()->create(['price' => 100]);
    $cart = Cart::create(['user_id' => $user->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'itemable_id' => $service->id,
        'itemable_type' => Service::class,
        'name' => $service->name,
        'price' => $service->price,
        'quantity' => 1,
    ]);

    $bookingService = new BookingService;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('An active subscription is required to book maintenance services.');

    $bookingService->createFromCart($user, [
        'address_id' => 'some-id',
        'scheduled_at' => now()->addDay(),
        'notes' => 'Test booking',
    ]);
});
