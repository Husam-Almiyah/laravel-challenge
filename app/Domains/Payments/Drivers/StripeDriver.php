<?php

namespace App\Domains\Payments\Drivers;

use App\Domains\Payments\Contracts\PaymentGatewayInterface;
use App\Domains\Payments\Data\PaymentResponse;
use App\Domains\Payments\Models\Transaction;
use App\Domains\Payments\States\Paid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeDriver implements PaymentGatewayInterface
{
    /**
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'stripe';
    }

    public function pay(Transaction $transaction, array $options = []): PaymentResponse
    {
        // Mocking: stripe often requires a redirect for 3DS
        if ($transaction->amount > 500) {
            return PaymentResponse::redirect('https://stripe.com/mock-checkout/'.$transaction->id, 'STRIPE-'.uniqid());
        }

        $transaction->status->transitionTo(Paid::class);
        $transaction->update(['reference' => 'STRIPE-'.uniqid()]);

        return PaymentResponse::successful($transaction->reference, 'Stripe payment processed.');
    }

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
        Log::channel('payments')->info('Stripe callback received', [
            'transaction_id' => $transaction->id,
            'data' => $data,
        ]);

        // Mock: Verify payment intent with Stripe API
        return $this->verify($transaction);
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        Log::channel('payments')->info('Stripe webhook received', [
            'payload' => $request->all(),
        ]);

        // Mock: Verify signature and process webhook event
        // In real implementation, verify Stripe signature here
        $eventType = $request->input('type');

        Log::channel('payments')->info('Stripe webhook event', [
            'event_type' => $eventType,
        ]);

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
            'redirect_url' => $transaction->amount > 500 ? 'https://stripe.com/mock-checkout/'.$transaction->id : null,
        ], $extra);
    }
}
