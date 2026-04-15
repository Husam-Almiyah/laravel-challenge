<?php

namespace App\Domains\Subscriptions\Services;

use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Activate a trial subscription for a user.
     *
     * @throws \Exception
     */
    public function activateTrial(User $user, int|string $planId): Subscription
    {
        // Check if user already has an active subscription
        $existingSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            throw new \Exception('User already has an active subscription');
        }

        // Check if user already used trial
        if ($user->trial_used_at) {
            throw new \Exception('Trial subscription already used');
        }

        $plan = SubscriptionPlan::findOrFail($planId);

        if ($plan->trial_days <= 0) {
            throw new \Exception('This plan does not offer a trial');
        }

        return DB::transaction(function () use ($user, $plan) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->trial_days),
                'status' => 'active',
                'is_trial' => true,
            ]);

            $user->update(['trial_used_at' => now()]);

            return $subscription;
        });
    }
}
