<?php

namespace App\Domains\Payments\Drivers;

use App\Domains\Payments\Contracts\PaymentGatewayInterface;
use App\Domains\Payments\Data\PaymentResponse;
use App\Domains\Payments\Models\Transaction;
use App\Domains\Payments\States\Failed;
use App\Domains\Payments\States\Paid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MadaDriver implements PaymentGatewayInterface
{
    /**
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'mada';
    }

    /**
     * Process a Mada payment.
     *
     * Mock behavior (for demo/testing only):
     * - Amount of exactly 404.00 will always fail
     * - All other amounts succeed immediately
     *
     * @mock-only In production, replace with actual Mada API integration
     */
    public function pay(Transaction $transaction, array $options = []): PaymentResponse
    {
        // Mocking logic: If amount is exactly 404, fail it. Otherwise, success.
        // This simulates a gateway-declined transaction for testing error flows.
        if ($transaction->amount == 404.00) {
            $transaction->status->transitionTo(Failed::class);

            return PaymentResponse::failed('Mada payment failed (Mocked 404).');
        }

        $transaction->status->transitionTo(Paid::class);
        $transaction->update(['reference' => 'MADA-'.uniqid()]);

        return PaymentResponse::successful($transaction->reference, 'Mada payment processed successfully.');
    }

    /**
     * Verify the payment status from the Mada gateway.
     *
     * @mock-only Always returns success in demo mode
     */
    public function verify(Transaction $transaction): PaymentResponse
    {
        return PaymentResponse::successful($transaction->reference, 'Transaction verified.');
    }

    public function hasCallback(): bool
    {
        return true;
    }

    public function hasWebhook(): bool
    {
        return true;
    }

    public function handleCallback(Transaction $transaction, array $data = []): PaymentResponse
    {
        Log::channel('payments')->info('Mada callback received', [
            'transaction_id' => $transaction->id,
            'data' => $data,
        ]);

        // Mock: Verify with Mada API and update status
        return $this->verify($transaction);
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        Log::channel('payments')->info('Mada webhook received', [
            'payload' => $request->all(),
        ]);

        // Mock: Process webhook and update transaction
        return PaymentResponse::successful(null, 'Webhook processed.');
    }

    public function buildResponse(Transaction $transaction, array $extra = []): array
    {
        return array_merge([
            'gateway' => $this->getName(),
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'status' => (string) $transaction->status,
        ], $extra);
    }
}
