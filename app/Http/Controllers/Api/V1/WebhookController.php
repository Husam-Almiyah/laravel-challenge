<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Payments\Events\WebhookReceived;
use App\Domains\Payments\PaymentManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager
    ) {}

    /**
     * Handle webhook notifications from payment gateways.
     * POST /api/v1/payments/webhooks/{gatewayName}
     */
    public function handle(Request $request, string $gatewayName): JsonResponse
    {
        Log::channel('payments')->info('Webhook endpoint hit', [
            'gateway' => $gatewayName,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            // Resolve the gateway driver
            $driver = $this->paymentManager->resolveByName($gatewayName);

            // Check if gateway supports webhooks
            if (! $driver->hasWebhook()) {
                return response()->json([
                    'success' => false,
                    'message' => "Webhook not supported for {$gatewayName}",
                ], 400);
            }

            // Dispatch webhook received event
            event(new WebhookReceived($gatewayName, $request->all()));

            // Handle the webhook
            $response = $driver->handleWebhook($request);

            return response()->json([
                'success' => $response->success,
                'message' => $response->message,
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::channel('payments')->error('Invalid gateway in webhook', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gateway not supported',
            ], 404);
        } catch (\Exception $e) {
            Log::channel('payments')->error('Webhook processing failed', [
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
            ], 500);
        }
    }
}
