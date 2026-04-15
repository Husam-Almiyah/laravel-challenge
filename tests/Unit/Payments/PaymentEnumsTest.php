<?php

namespace Tests\Unit\Payments;

use App\Domains\Payments\Enums\PaymentMethodEnum;
use App\Domains\Payments\Enums\TransactionTypeEnum;

test('payment method enum has correct values', function () {
    expect(PaymentMethodEnum::Mada->value)->toBe('mada')
        ->and(PaymentMethodEnum::Stripe->value)->toBe('stripe')
        ->and(PaymentMethodEnum::ApplePay->value)->toBe('apple_pay');
});

test('payment method enum returns correct labels', function () {
    expect(PaymentMethodEnum::Mada->label())->toBe('Mada')
        ->and(PaymentMethodEnum::Stripe->label())->toBe('Stripe')
        ->and(PaymentMethodEnum::ApplePay->label())->toBe('Apple Pay');
});

test('payment method enum returns correct drivers', function () {
    expect(PaymentMethodEnum::Mada->driver())->toBe('MadaDriver')
        ->and(PaymentMethodEnum::Stripe->driver())->toBe('StripeDriver')
        ->and(PaymentMethodEnum::ApplePay->driver())->toBe('ApplePayDriver');
});

test('payment method enum identifies redirect requirement', function () {
    expect(PaymentMethodEnum::Stripe->requiresRedirect())->toBeTrue()
        ->and(PaymentMethodEnum::Mada->requiresRedirect())->toBeFalse()
        ->and(PaymentMethodEnum::ApplePay->requiresRedirect())->toBeFalse();
});

test('payment method enum identifies webhook support', function () {
    expect(PaymentMethodEnum::Stripe->supportsWebhooks())->toBeTrue()
        ->and(PaymentMethodEnum::Mada->supportsWebhooks())->toBeTrue()
        ->and(PaymentMethodEnum::ApplePay->supportsWebhooks())->toBeFalse();
});

test('payment method enum converts to array', function () {
    $array = PaymentMethodEnum::toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('mada', 'Mada')
        ->and($array)->toHaveKey('stripe', 'Stripe')
        ->and($array)->toHaveKey('apple_pay', 'Apple Pay');
});

test('transaction type enum has correct values', function () {
    expect(TransactionTypeEnum::Payment->value)->toBe('payment')
        ->and(TransactionTypeEnum::Refund->value)->toBe('refund')
        ->and(TransactionTypeEnum::Capture->value)->toBe('capture')
        ->and(TransactionTypeEnum::Authorization->value)->toBe('authorization');
});

test('transaction type enum identifies debit types', function () {
    expect(TransactionTypeEnum::Payment->isDebit())->toBeTrue()
        ->and(TransactionTypeEnum::Capture->isDebit())->toBeTrue()
        ->and(TransactionTypeEnum::Authorization->isDebit())->toBeTrue()
        ->and(TransactionTypeEnum::Refund->isDebit())->toBeFalse();
});
