<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Subscriptions\Models\Subscription;
use App\Domains\Subscriptions\Models\SubscriptionPlan;
use App\Domains\Subscriptions\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * List available subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => SubscriptionPlanResource::collection($plans),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page' => $plans->lastPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
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
    public function activateTrial(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        try {
            $subscription = $this->subscriptionService->activateTrial(
                $request->user(),
                $request->plan_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Trial subscription activated',
                'data' => [
                    'subscription' => new SubscriptionResource($subscription->load('plan')),
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
