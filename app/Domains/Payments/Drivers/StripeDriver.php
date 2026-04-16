<?php

namespace App\Domains\Payments\Drivers;

use App\Domains\Payments\Contracts\PaymentGatewayInterface;
use App\Domains\Payments\Data\PaymentResponse;
use App\Domains\Payments\Jobs\ProcessStripeWebhookJob;
use App\Domains\Payments\Models\Transaction;
use App\Domains\Payments\States\Paid;
use App\Domains\Payments\Traits\VerifiesWebhookSignatures;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeDriver implements PaymentGatewayInterface
{
    use VerifiesWebhookSignatures;

    /**
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'stripe';
    }

    /**
     * Process a Stripe payment.
     *
     * Mock behavior (for demo/testing only):
     * - Amounts over 500 trigger a 3D Secure redirect flow
     * - Amounts <= 500 succeed immediately
     *
     * @mock-only In production, replace with actual Stripe SDK integration
     */
    public function pay(Transaction $transaction, array $options = []): PaymentResponse
    {
        // Mocking: stripe often requires a redirect for 3DS
        // This simulates the redirect flow for larger amounts
        if ($transaction->amount > 500) {
            return PaymentResponse::redirect('https://stripe.com/mock-checkout/'.$transaction->id, 'STRIPE-'.uniqid());
        }

        $transaction->status->transitionTo(Paid::class);
        $transaction->update(['reference' => 'STRIPE-'.uniqid()]);

        return PaymentResponse::successful($transaction->reference, 'Stripe payment processed.');
    }

    /**
     * Verify the payment status from the Stripe gateway.
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
        Log::channel('payments')->info('Stripe callback received', [
            'transaction_id' => $transaction->id,
            'data' => $data,
        ]);

        // Mock: Verify payment intent with Stripe API
        return $this->verify($transaction);
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        // Verify webhook signature if present
        if (! $this->verifyWebhookSignature($request)) {
            Log::channel('payments')->warning('Stripe webhook signature verification failed', [
                'ip' => $request->ip(),
            ]);

            return PaymentResponse::failed('Invalid webhook signature.');
        }

        Log::channel('payments')->info('Stripe webhook received, dispatching job', [
            'payload' => $request->all(),
        ]);

        // Dispatch the job for background processing
        ProcessStripeWebhookJob::dispatch($request->all());

        return PaymentResponse::successful(null, 'Webhook received and queued.');
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
