<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use App\Domains\Subscriptions\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\ActivateTrialRequest;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * List available subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => SubscriptionPlanResource::collection($plans),
            ],
        ]);
    }

    /**
     * Get user's current subscription.
     */
    public function mySubscription(Request $request): JsonResponse
    {
        $subscription = Subscription::with('plan')
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        if (! $subscription) {
            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => null,
                    'message' => 'No active subscription',
                ],
            ]);
        }

        $daysRemaining = now()->diffInDays($subscription->ends_at, false);

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => new SubscriptionResource($subscription),
            ],
        ]);
    }

    /**
     * Activate trial subscription.
     */
    public function activateTrial(ActivateTrialRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $subscriptionService = new SubscriptionService;
            $subscription = $subscriptionService->activateTrial($user, $request->plan_id);

            return response()->json([
                'success' => true,
                'message' => 'Trial subscription activated',
                'data' => [
                    'subscription' => new SubscriptionResource($subscription),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
