<?php

use App\Domains\Payments\Drivers\StripeDriver;
use App\Domains\Payments\Jobs\ProcessStripeWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

test('stripe driver dispatches background job for webhooks', function () {
    Bus::fake();

    $driver = new StripeDriver;
    // Use Request::create for more reliable mock requests
    $request = Request::create('/api/webhook', 'POST', [
        'type' => 'payment_intent.succeeded',
        'data' => ['id' => 'pi_123'],
    ]);

    $response = $driver->handleWebhook($request);

    expect($response->success)->toBeTrue();
    Bus::assertDispatched(ProcessStripeWebhookJob::class, function ($job) {
        $reflection = new ReflectionProperty($job, 'payload');
        $reflection->setAccessible(true);
        $payload = $reflection->getValue($job);

        return isset($payload['type']) && $payload['type'] === 'payment_intent.succeeded';
    });
});

test('stripe webhook job processes payload', function () {
    Log::shouldReceive('channel')->with('payments')->andReturnSelf();
    Log::shouldReceive('info')->atLeast()->once();

    $payload = ['type' => 'payment_intent.succeeded'];
    $job = new ProcessStripeWebhookJob($payload);

    $job->handle();
});
