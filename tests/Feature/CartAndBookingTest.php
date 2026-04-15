<?php

use App\Domains\Booking\Models\Cart;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Service;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->city = City::create(['name' => 'Test City', 'slug' => 'test-city', 'is_active' => true]);
    $this->address = $this->user->addresses()->create([
        'city_id' => $this->city->id,
        'district' => 'Downtown',
        'address_details' => '123 Main St',
    ]);
    $this->category = Category::factory()->create();
    $this->service = Service::create([
        'category_id' => $this->category->id,
        'name' => 'Cleaning',
        'slug' => 'cleaning',
        'description' => 'Home cleaning',
        'price' => 50,
        'is_active' => true,
    ]);
});

it('can add item to cart', function () {
    $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
        'itemable_id' => $this->service->id,
        'itemable_type' => Service::class,
        'quantity' => 2,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('cart_items', [
        'itemable_id' => $this->service->id,
        'quantity' => 2,
    ]);
});

it('can checkout cart into booking', function () {
    // Add item directly to ease testing
    $cart = Cart::create(['user_id' => $this->user->id]);
    $cart->items()->create([
        'itemable_id' => $this->service->id,
        'itemable_type' => Service::class,
        'name' => $this->service->name,
        'price' => $this->service->price,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($this->user)->postJson('/api/v1/bookings', [
        'scheduled_at' => now()->addDays(2)->toDateTimeString(),
        'address_id' => $this->address->id,
        'notes' => 'Please bring own equipment',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('bookings', [
        'user_id' => $this->user->id,
        'address_id' => $this->address->id,
        'total_amount' => 50,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('booking_items', [
        'itemable_id' => $this->service->id,
    ]);

    // Cart is cleared
    expect($cart->fresh()->items)->toBeEmpty();
});
