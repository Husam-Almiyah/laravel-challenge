<?php

namespace App\Domains\Payments\Contracts;

use App\Domains\Payments\Data\PaymentResponse;
use App\Domains\Payments\Models\Transaction;
use Illuminate\Http\Request;

/**
 * Interface for payment gateway implementations.
 * Each gateway driver must implement this contract.
 */
interface PaymentGatewayInterface
{
    /**
     * Get the gateway name/identifier.
     */
    public function getName(): string;

    /**
     * Initiate the payment process.
     */
    public function pay(Transaction $transaction, array $options = []): PaymentResponse;

    /**
     * Verify the payment status from the gateway.
     */
    public function verify(Transaction $transaction): PaymentResponse;

    /**
     * Check if this gateway supports callbacks (redirect-based payment flow).
     */
    public function hasCallback(): bool;

    /**
     * Check if this gateway supports webhooks (async notifications).
     */
    public function hasWebhook(): bool;

    /**
     * Handle a callback from the payment gateway (after redirect).
     */
    public function handleCallback(Transaction $transaction, array $data = []): PaymentResponse;

    /**
     * Handle a webhook notification from the gateway.
     */
    public function handleWebhook(Request $request): PaymentResponse;

    /**
     * Build a client-facing response payload.
     */
    public function buildResponse(Transaction $transaction, array $extra = []): array;
}
