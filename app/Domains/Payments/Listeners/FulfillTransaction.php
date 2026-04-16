<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payments\Events\PaymentSucceeded;
use App\Domains\Payments\States\Completed;
use App\Domains\Subscriptions\Enums\SubscriptionStatus;
use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Log;

class FulfillTransaction
{
    /**
     * Handle the event.
     */
    public function handle(PaymentSucceeded $event): void
    {
        $transaction = $event->transaction;
        $payable = $transaction->payable;

        if (! $payable) {
            Log::channel('payments')->error('Transaction payable not found during fulfillment', [
                'transaction_id' => $transaction->id,
            ]);

            return;
        }

        try {
            if ($payable instanceof Booking) {
                $this->fulfillBooking($payable);
            } elseif ($payable instanceof SubscriptionPlan) {
                $this->fulfillSubscription($transaction, $payable);
            }

            // Transition transaction to completed
            if ($transaction->status->canTransitionTo(Completed::class)) {
                $transaction->status->transitionTo(Completed::class);
            }

        } catch (\Exception $e) {
            Log::channel('payments')->error('Fulfillment failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Fulfill a booking transaction.
     */
    protected function fulfillBooking(Booking $booking): void
    {
        $booking->update([
            'status' => BookingStatus::CONFIRMED,
        ]);

        Log::channel('payments')->info('Booking fulfilled', [
            'booking_id' => $booking->id,
        ]);
    }

    /**
     * Fulfill a subscription plan purchase.
     */
    protected function fulfillSubscription($transaction, SubscriptionPlan $plan): void
    {
        $user = $transaction->payer;

        // Validate user doesn't already have an active subscription
        $existingSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            Log::channel('payments')->warning('Subscription fulfillment skipped - user already has active subscription', [
                'user_id' => $user->id,
                'existing_subscription_id' => $existingSubscription->id,
            ]);

            return;
        }

        // Validate trial usage if this is a trial plan
        if ($plan->trial_days > 0 && $user->trial_used_at) {
            Log::channel('payments')->warning('Subscription fulfillment skipped - trial already used', [
                'user_id' => $user->id,
            ]);

            return;
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => $plan->trial_days > 0,
        ]);

        // Mark trial as used if applicable
        if ($plan->trial_days > 0) {
            $user->update(['trial_used_at' => now()]);
        }

        Log::channel('payments')->info('Subscription fulfilled', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'is_trial' => $plan->trial_days > 0,
        ]);
    }
}
