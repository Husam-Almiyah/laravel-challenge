<?php

namespace App\Domains\Payments\Data;

/**
 * DTO for webhook payload data.
 */
class WebhookData
{
    public function __construct(
        public readonly string $gatewayName,
        public readonly ?string $transactionReference = null,
        public readonly ?string $eventId = null,
        public readonly ?string $eventType = null,
        public readonly array $payload = [],
        public readonly ?string $signature = null,
    ) {}

    /**
     * Create from request.
     */
    public static function fromRequest(string $gatewayName, array $data, ?string $signature = null): self
    {
        return new self(
            gatewayName: $gatewayName,
            transactionReference: $data['reference'] ?? $data['id'] ?? null,
            eventId: $data['event_id'] ?? null,
            eventType: $data['type'] ?? $data['event_type'] ?? null,
            payload: $data,
            signature: $signature,
        );
    }
}
