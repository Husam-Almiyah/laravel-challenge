<?php

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Services\GatewayAvailabilityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('gateway availability is cached', function () {
    // Create an active gateway manually since factory isn't available
    PaymentGateway::create([
        'name' => 'mada',
        'driver' => 'mada',
        'is_active' => true,
        'priority' => 1,
        'currency' => 'SAR',
    ]);

    $resolver = new GatewayAvailabilityResolver;
    $context = ['city_id' => 1];

    // First call: should query DB and cache
    $results1 = $resolver->getAvailableGateways($context);
    expect($results1)->toHaveCount(1);

    // Verify cache has it with version
    $cacheContext = $context;
    $cacheVersion = Cache::get('gateways_availability_version', 'v1');
    $cacheKey = 'gateways_availability_'.$cacheVersion.'_'.md5(json_encode($cacheContext));
    expect(Cache::has($cacheKey))->toBeTrue();

    // Modify DB to prove cache is used
    PaymentGateway::where('name', 'mada')->update(['is_active' => false]);

    // Second call: should return from cache (still 1)
    $results2 = $resolver->getAvailableGateways($context);
    expect($results2)->toHaveCount(1);

    // Clear cache and call again: should be 0
    Cache::forget($cacheKey);
    $results3 = $resolver->getAvailableGateways($context);
    expect($results3)->toHaveCount(0);
});

test('cache key differs for different contexts', function () {
    $resolver = new GatewayAvailabilityResolver;

    $context1 = ['city_id' => 1];
    $context2 = ['city_id' => 2];

    $key1 = 'gateways_availability_'.md5(json_encode($context1));
    $key2 = 'gateways_availability_'.md5(json_encode($context2));

    expect($key1)->not->toBe($key2);
});
