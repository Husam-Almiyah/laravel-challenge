<?php

namespace App\Domains\Subscriptions\Services;

use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Activate a trial subscription for a user.
     *
     * Uses pessimistic locking (lockForUpdate) to prevent race conditions
     * where two concurrent requests could create duplicate active subscriptions.
     *
     * @throws \InvalidArgumentException
     */
    public function activateTrial(User $user, int|string $planId): Subscription
    {
        $plan = SubscriptionPlan::findOrFail($planId);

        if ($plan->trial_days <= 0) {
            throw new \InvalidArgumentException('This plan does not offer a trial');
        }

        return DB::transaction(function () use ($user, $plan) {
            // Use pessimistic locking to prevent race conditions
            $existingSubscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if ($existingSubscription) {
                throw new \InvalidArgumentException('User already has an active subscription');
            }

            // Check if user already used trial (also under lock)
            $user = User::lockForUpdate()->find($user->id);

            if ($user->trial_used_at) {
                throw new \InvalidArgumentException('Trial subscription already used');
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->trial_days),
                'status' => 'active',
                'is_trial' => true,
            ]);

            $user->update(['trial_used_at' => now()]);

            Log::info('Trial subscription activated', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'subscription_id' => $subscription->id,
            ]);

            return $subscription;
        });
    }

    /**
     * Check if a user currently has an active subscription.
     */
    public function isCurrentlySubscribed(User $user): bool
    {
        return $user->hasActiveSubscription();
    }
}
