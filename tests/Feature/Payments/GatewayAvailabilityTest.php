<?php

use App\Domains\Payments\Models\PaymentGateway;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    City::create(['id' => 1, 'name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);
    City::create(['id' => 2, 'name' => 'Jeddah', 'slug' => 'jeddah', 'is_active' => true]);
});

test('gateway unavailable when city not in allowed list', function () {
    PaymentGateway::create([
        'name' => 'mada',
        'driver' => 'mada',
        'rules' => ['cities' => [1]], // Only Riyadh
        'is_active' => true,
    ]);

    // Request with Jeddah city (id=2)
    $response = $this->getJson('/api/v1/payments/gateways?city_id=2&amount=100');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('gateway unavailable when inactive', function () {
    PaymentGateway::create([
        'name' => 'stripe',
        'driver' => 'stripe',
        'rules' => [],
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/v1/payments/gateways?city_id=1&amount=100');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('gateway unavailable when amount below minimum', function () {
    PaymentGateway::create([
        'name' => 'stripe',
        'driver' => 'stripe',
        'rules' => ['min_amount' => 50],
        'is_active' => true,
    ]);

    // Try with amount below minimum
    $response = $this->getJson('/api/v1/payments/gateways?city_id=1&amount=30');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');

    // Try with amount above minimum
    $response = $this->getJson('/api/v1/payments/gateways?city_id=1&amount=60');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('gateway unavailable on restricted days', function () {
    PaymentGateway::create([
        'name' => 'stripe',
        'driver' => 'stripe',
        'rules' => ['allowed_days' => [1, 2, 3, 4, 5]], // Monday to Friday
        'is_active' => true,
    ]);

    // Mock weekend (Saturday = 6, Sunday = 0)
    $today = now()->dayOfWeek;
    $isWeekend = in_array($today, [0, 6]);

    $response = $this->getJson('/api/v1/payments/gateways?city_id=1&amount=100');

    if ($isWeekend) {
        $response->assertJsonCount(0, 'data');
    } else {
        $response->assertJsonCount(1, 'data');
    }
});

test('payment initiation fails with unavailable gateway', function () {
    $gateway = PaymentGateway::create([
        'name' => 'mada',
        'driver' => 'mada',
        'rules' => ['cities' => [1]], // Only Riyadh
        'is_active' => true,
    ]);

    $payload = [
        'gateway_id' => $gateway->id,
        'amount' => 100,
        'city_id' => 2, // Jeddah - not allowed
        'payer_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payer_type' => 'App\\Models\\User',
        'payable_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payable_type' => 'App\\Domains\\Subscriptions\\Models\\Subscription',
    ];

    $response = $this->postJson('/api/v1/payments/initiate', $payload);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Gateway not available for this context',
        ]);
});
