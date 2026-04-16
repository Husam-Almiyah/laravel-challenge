<?php

use App\Domains\Account\Enums\UserStatus;
use App\Domains\Catalog\Enums\ModuleEnum;
use App\Domains\Payments\Models\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('gateway rules cities must contain only integers when saving', function () {
    $gateway = new PaymentGateway([
        'name' => 'Test Gateway 1',
        'driver' => 'mada',
        'rules' => [
            'cities' => [1, '2'], // '2' is a string, not an integer
        ],
    ]);

    $gateway->save();
})->throws(InvalidArgumentException::class, "Gateway rule 'cities' must contain only integers");

test('gateway rules modules must be allowed values', function () {
    $gateway = new PaymentGateway([
        'name' => 'Test Gateway 2',
        'driver' => 'mada',
        'rules' => [
            'modules' => ['booking', 'invalid'], // 'invalid' is not allowed
        ],
    ]);

    $gateway->save();
})->throws(InvalidArgumentException::class, "Gateway rule 'modules' must contain only allowed modules: booking, subscription");

test('gateway rules required_status must be verified or guest', function () {
    $gateway = new PaymentGateway([
        'name' => 'Test Gateway 3',
        'driver' => 'mada',
        'rules' => [
            'required_status' => 'premium', // 'premium' is not allowed
        ],
    ]);

    $gateway->save();
})->throws(InvalidArgumentException::class, "Gateway rule 'required_status' must be one of: active, inactive, suspended, verified, guest");

test('gateway rules allowed_days must be integers between 0 and 6', function () {
    $gateway = new PaymentGateway([
        'name' => 'Test Gateway 4',
        'driver' => 'mada',
        'rules' => [
            'allowed_days' => [0, 7], // 7 is invalid
        ],
    ]);

    $gateway->save();
})->throws(InvalidArgumentException::class, "Gateway rule 'allowed_days' must contain integers between 0 and 6");

test('gateway rules save successfully with valid data using enums', function () {
    $gateway = new PaymentGateway([
        'name' => 'Test Gateway 5',
        'driver' => 'mada',
        'rules' => [
            'cities' => [1, 2],
            'modules' => [ModuleEnum::BOOKING->value, ModuleEnum::SUBSCRIPTION->value],
            'required_status' => UserStatus::VERIFIED->value,
            'min_amount' => 10.50,
            'allowed_days' => [0, 5, 6],
        ],
    ]);

    $gateway->save();

    expect($gateway->rules['cities'])->toBe([1, 2])
        ->and($gateway->rules['modules'])->toBe(['booking', 'subscription'])
        ->and($gateway->rules['required_status'])->toBe('verified')
        ->and($gateway->rules['min_amount'])->toBe(10.50)
        ->and($gateway->rules['allowed_days'])->toBe([0, 5, 6]);
});
