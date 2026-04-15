<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Payments\Events\PaymentFailed;
use App\Domains\Payments\Events\PaymentInitiated;
use App\Domains\Payments\Events\PaymentSucceeded;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\Transaction;
use App\Domains\Payments\PaymentManager;
use App\Domains\Payments\Resources\PaymentGatewayResource;
use App\Domains\Payments\Resources\TransactionResource;
use App\Domains\Payments\Services\GatewayAvailabilityResolver;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\InitiatePaymentRequest;
use App\Http\Requests\Payments\ListGatewaysRequest;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected GatewayAvailabilityResolver $availabilityResolver
    ) {}

    /**
     * List available gateways based on context.
     */
    public function index(ListGatewaysRequest $request): JsonResponse
    {

        $gateways = $this->availabilityResolver->getAvailableGateways([
            'city_id' => $request->city_id,
            'amount' => $request->amount,
            'module' => $request->module,
            'user' => $request->user(),
            'user_status' => $request->user()?->status ?? 'guest',
            'day_of_week' => now()->dayOfWeek,
        ]);

        return response()->json([
            'success' => true,
            'data' => PaymentGatewayResource::collection($gateways),
        ]);
    }

    /**
     * Initiate a payment.
     */
    public function store(InitiatePaymentRequest $request): JsonResponse
    {

        $gateway = PaymentGateway::findOrFail($request->gateway_id);

        // 1. Create Transaction (State Machine defaults to 'pending')
        $transaction = Transaction::create([
            'payer_id' => $request->payer_id,
            'payer_type' => $request->payer_type,
            'payable_id' => $request->payable_id,
            'payable_type' => $request->payable_type,
            'gateway_id' => $gateway->id,
            'amount' => $request->amount,
            'currency' => $gateway->currency,
        ]);

        // 2. Dispatch PaymentInitiated event
        event(new PaymentInitiated($transaction));

        // 3. Resolve Driver and Pay
        try {
            $driver = $this->paymentManager->driver($gateway->driver);
            $response = $driver->pay($transaction);

            // 4. Dispatch success/failure event
            if ($response->success) {
                event(new PaymentSucceeded($transaction));
            } else {
                event(new PaymentFailed($transaction, $response->message));
            }

            // 5. Build response using driver's method
            return response()->json([
                'success' => $response->success,
                'status' => $response->status,
                'message' => $response->message,
                'action_url' => $response->actionUrl,
                'transaction' => new TransactionResource($transaction),
            ]);
        } catch (\Exception $e) {
            // Dispatch failure event
            event(new PaymentFailed($transaction, $e->getMessage()));

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
