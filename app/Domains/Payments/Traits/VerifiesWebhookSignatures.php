<?php

namespace App\Domains\Payments\Traits;

use Illuminate\Http\Request;

/**
 * Trait for verifying webhook signatures from payment gateways.
 *
 * Each gateway driver can implement its own signature verification logic
 * by using this trait and overriding the getWebhookSecret() method.
 */
trait VerifiesWebhookSignatures
{
    /**
     * Verify the webhook signature from the request.
     *
     * @param  Request  $request  The incoming webhook request
     * @param  string|null  $signature  The signature to verify (extracted from headers or payload)
     * @return bool Whether the signature is valid
     */
    public function verifyWebhookSignature(Request $request, ?string $signature = null): bool
    {
        // If no signature provided, try to extract from common headers
        $signature = $signature
            ?? $request->header('X-Signature')
            ?? $request->header('X-Hub-Signature')
            ?? $request->header('X-Signature-256')
            ?? $request->input('signature');

        // If still no signature, skip verification (allow configuration to enforce)
        if (! $signature) {
            return config('payments.webhooks.require_signature', false) === false;
        }

        $payload = $request->getContent();
        $secret = $this->getWebhookSecret();

        // Support different signature formats
        if (str_starts_with($signature, 'sha256=')) {
            $expectedHash = hash_hmac('sha256', $payload, $secret);

            return hash_equals($signature, 'sha256='.$expectedHash);
        }

        if (str_starts_with($signature, 'sha512=')) {
            $expectedHash = hash_hmac('sha512', $payload, $secret);

            return hash_equals($signature, 'sha512='.$expectedHash);
        }

        // Raw signature comparison
        $expectedHash = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $expectedHash);
    }

    /**
     * Get the webhook secret for this gateway.
     *
     * Override this method in your driver to provide the secret.
     */
    protected function getWebhookSecret(): string
    {
        return config('payments.webhook_secret', '');
    }
}
