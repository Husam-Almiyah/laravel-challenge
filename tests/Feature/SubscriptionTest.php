<?php

use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->plan = SubscriptionPlan::create([
        'name' => 'Pro Plan',
        'slug' => 'pro-plan',
        'details' => 'Pro plan details',
        'price' => 100,
        'duration_days' => 30,
        'trial_days' => 14,
        'is_active' => true,
    ]);
});

it('can fetch subscription plans', function () {
    $response = $this->getJson('/api/v1/subscriptions/plans');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

it('can activate trial subscription', function () {
    $response = $this->actingAs($this->user)->postJson('/api/v1/subscriptions/trial', [
        'plan_id' => $this->plan->id,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Trial subscription activated',
        ]);

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'is_trial' => true,
    ]);

    expect($this->user->fresh()->trial_used_at)->not->toBeNull();
});

it('cannot activate trial twice', function () {
    $this->actingAs($this->user)->postJson('/api/v1/subscriptions/trial', [
        'plan_id' => $this->plan->id,
    ]);

    // Fast forward to mock ending of trial
    $this->user->update(['trial_used_at' => now()->subDays(15)]);

    Subscription::where('user_id', $this->user->id)->update(['status' => 'expired']);

    // Try again
    $response = $this->actingAs($this->user)->postJson('/api/v1/subscriptions/trial', [
        'plan_id' => $this->plan->id,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Trial subscription already used',
        ]);
});
