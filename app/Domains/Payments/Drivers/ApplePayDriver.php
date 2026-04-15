<?php

namespace App\Domains\Payments\Drivers;

use App\Domains\Payments\Contracts\PaymentGatewayInterface;
use App\Domains\Payments\Data\PaymentResponse;
use App\Domains\Payments\Models\Transaction;
use App\Domains\Payments\States\Paid;
use Illuminate\Http\Request;

class ApplePayDriver implements PaymentGatewayInterface
{
    /**
     * Get the gateway name.
     */
    public function getName(): string
    {
        return 'apple_pay';
    }

    public function pay(Transaction $transaction, array $options = []): PaymentResponse
    {
        // Simple success mock
        $transaction->status->transitionTo(Paid::class);
        $transaction->update(['reference' => 'APPLE-'.uniqid()]);

        return PaymentResponse::successful($transaction->reference, 'Apple Pay processed successfully.');
    }

    public function verify(Transaction $transaction): PaymentResponse
    {
        return PaymentResponse::successful($transaction->reference, 'Transaction verified.');
    }

    public function hasCallback(): bool
    {
        return false; // Apple Pay is typically direct
    }

    public function hasWebhook(): bool
    {
        return false; // Apple Pay doesn't use webhooks in this mock
    }

    public function handleCallback(Transaction $transaction, array $data = []): PaymentResponse
    {
        // Not applicable for Apple Pay
        return PaymentResponse::failed('Callback not supported for Apple Pay.');
    }

    public function handleWebhook(Request $request): PaymentResponse
    {
        // Not applicable for Apple Pay
        return PaymentResponse::failed('Webhook not supported for Apple Pay.');
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
