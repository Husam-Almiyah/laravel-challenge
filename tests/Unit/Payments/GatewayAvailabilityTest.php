<?php

namespace Tests\Unit\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Services\GatewayAvailabilityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->resolver = new GatewayAvailabilityResolver;
});

test('it filters gateways by city', function () {
    // 1. Create a gateway restricted to Riyadh (ID 1)
    $mada = PaymentGateway::create([
        'name' => 'mada',
        'driver' => 'MadaDriver',
        'rules' => ['cities' => [1]],
        'is_active' => true,
    ]);

    // 2. Context for Riyadh (available)
    expect($this->resolver->isAvailable($mada, ['city_id' => 1]))->toBeTrue();

    // 3. Context for Jeddah (not available)
    expect($this->resolver->isAvailable($mada, ['city_id' => 2]))->toBeFalse();
});

test('it filters gateways by module', function () {
    $mada = PaymentGateway::create([
        'name' => 'mada',
        'driver' => 'MadaDriver',
        'rules' => ['modules' => ['booking']],
        'is_active' => true,
    ]);

    expect($this->resolver->isAvailable($mada, ['module' => 'booking']))->toBeTrue();
    expect($this->resolver->isAvailable($mada, ['module' => 'subscription']))->toBeFalse();
});

test('it filters gateways by user status', function () {
    $applePay = PaymentGateway::create([
        'name' => 'apple_pay',
        'driver' => 'ApplePayDriver',
        'rules' => ['required_status' => 'verified'],
        'is_active' => true,
    ]);

    expect($this->resolver->isAvailable($applePay, ['user_status' => 'verified']))->toBeTrue();
    expect($this->resolver->isAvailable($applePay, ['user_status' => 'guest']))->toBeFalse();
});

test('it filters gateways by allowed days', function () {
    $stripe = PaymentGateway::create([
        'name' => 'stripe',
        'driver' => 'StripeDriver',
        'rules' => ['allowed_days' => [1, 2, 3]], // Mon, Tue, Wed
        'is_active' => true,
    ]);

    expect($this->resolver->isAvailable($stripe, ['day_of_week' => 1]))->toBeTrue();
    expect($this->resolver->isAvailable($stripe, ['day_of_week' => 5]))->toBeFalse(); // Fri
});

test('it filters gateways by minimum amount', function () {
    // 1. Create a gateway with min_amount of 100
    $stripe = PaymentGateway::create([
        'name' => 'stripe',
        'driver' => 'StripeDriver',
        'rules' => ['min_amount' => 100],
        'is_active' => true,
    ]);

    // 2. Context with 50 (not available)
    expect($this->resolver->isAvailable($stripe, ['amount' => 50]))->toBeFalse();

    // 3. Context with 150 (available)
    expect($this->resolver->isAvailable($stripe, ['amount' => 150]))->toBeTrue();
});
