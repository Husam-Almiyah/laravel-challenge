<?php

namespace Tests\Feature\Payments;

use App\Domains\Payments\Events\PaymentFailed;
use App\Domains\Payments\Events\PaymentInitiated;
use App\Domains\Payments\Events\PaymentSucceeded;
use App\Domains\Payments\Models\PaymentGateway;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed initial data needed for tests
    City::create(['id' => 1, 'name' => 'Riyadh', 'slug' => 'riyadh']);

    PaymentGateway::create([
        'name' => 'mada',
        'driver' => 'mada',
        'rules' => ['cities' => [1]],
        'is_active' => true,
    ]);
});

test('it can list available gateways via api', function () {
    $this->getJson('/api/v1/payments/gateways?city_id=1&amount=100')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['name' => 'mada']);
});

test('it can initiate a payment successfully', function () {
    $payload = [
        'gateway_id' => PaymentGateway::first()->id,
        'amount' => 100,
        'city_id' => 1, // Required for gateway availability check
        'payer_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5', // Mock ULID
        'payer_type' => 'App\\Models\\User',
        'payable_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5', // Mock ULID
        'payable_type' => 'App\\Domains\\Subscriptions\\Models\\Subscription',
    ];

    $this->postJson('/api/v1/payments/initiate', $payload)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => true, 'status' => 'success'])
        ->assertJsonStructure([
            'transaction' => ['gateway', 'transaction_id', 'reference', 'amount', 'currency', 'status'],
        ]);
});

test('it handles failed payments', function () {
    $payload = [
        'gateway_id' => PaymentGateway::first()->id,
        'amount' => 404, // Our mock driver fails on 404
        'city_id' => 1, // Required for gateway availability check
        'payer_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payer_type' => 'App\\Models\\User',
        'payable_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payable_type' => 'App\\Domains\\Subscriptions\\Models\\Subscription',
    ];
    $this->postJson('/api/v1/payments/initiate', $payload)
        ->assertStatus(200)
        ->assertJsonFragment(['success' => false, 'status' => 'failed']);
});

// Webhook Tests

test('webhook endpoint processes valid webhooks', function () {
    $this->postJson('/api/v1/payments/webhooks/mada', [
        'event_type' => 'payment.completed',
        'reference' => 'MADA-123',
    ])
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Webhook processed.',
        ]);
});

test('webhook endpoint rejects unsupported gateways', function () {
    $this->postJson('/api/v1/payments/webhooks/unknown', [
        'event_type' => 'payment.completed',
    ])
        ->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Gateway not supported',
        ]);
});

test('webhook endpoint rejects gateways without webhook support', function () {
    PaymentGateway::create([
        'name' => 'apple_pay',
        'driver' => 'applePay',
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/payments/webhooks/apple_pay', [
        'event_type' => 'payment.completed',
    ])
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'message' => 'Webhook not supported for apple_pay',
        ]);
});

// Event Dispatch Tests

test('payment initiated event is dispatched', function () {
    Event::fake();

    $payload = [
        'gateway_id' => PaymentGateway::first()->id,
        'amount' => 100,
        'city_id' => 1, // Required for gateway availability check
        'payer_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payer_type' => 'App\\Models\\User',
        'payable_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payable_type' => 'App\\Domains\\Subscriptions\\Models\\Subscription',
    ];

    $this->postJson('/api/v1/payments/initiate', $payload)
        ->assertStatus(200);

    Event::assertDispatched(PaymentInitiated::class);
});

test('payment succeeded event is dispatched on successful payment', function () {
    Event::fake();

    $payload = [
        'gateway_id' => PaymentGateway::first()->id,
        'amount' => 100,
        'city_id' => 1, // Required for gateway availability check
        'payer_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payer_type' => 'App\\Models\\User',
        'payable_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payable_type' => 'App\\Domains\\Subscriptions\\Models\\Subscription',
    ];

    $this->postJson('/api/v1/payments/initiate', $payload)
        ->assertStatus(200)
        ->assertJson(['success' => true]);

    Event::assertDispatched(PaymentSucceeded::class);
});

test('payment failed event is dispatched on failed payment', function () {
    Event::fake();

    $payload = [
        'gateway_id' => PaymentGateway::first()->id,
        'amount' => 404,
        'city_id' => 1, // Required for gateway availability check
        'payer_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payer_type' => 'App\\Models\\User',
        'payable_id' => '01H7B6K5X5H5X5H5X5H5X5H5X5',
        'payable_type' => 'App\\Domains\\Subscriptions\\Models\\Subscription',
    ];

    $this->postJson('/api/v1/payments/initiate', $payload)
        ->assertStatus(200)
        ->assertJson(['success' => false]);

    Event::assertDispatched(PaymentFailed::class);
});
