<?php

namespace App\Domains\Payments\Data;

/**
 * DTO for initiating a payment transaction.
 * Provides type-safe data transfer for payment requests.
 */
class InitiatePaymentData
{
    public function __construct(
        public readonly int $gatewayId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $payerId,
        public readonly string $payerType,
        public readonly string $payableId,
        public readonly string $payableType,
        public readonly array $metadata = [],
        public readonly ?string $description = null,
    ) {}

    /**
     * Create from request array.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            gatewayId: $data['gateway_id'],
            amount: (float) $data['amount'],
            currency: $data['currency'] ?? 'SAR',
            payerId: $data['payer_id'],
            payerType: $data['payer_type'],
            payableId: $data['payable_id'],
            payableType: $data['payable_type'],
            metadata: $data['metadata'] ?? [],
            description: $data['description'] ?? null,
        );
    }

    /**
     * Convert to array for storage.
     */
    public function toArray(): array
    {
        return [
            'gateway_id' => $this->gatewayId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payer_id' => $this->payerId,
            'payer_type' => $this->payerType,
            'payable_id' => $this->payableId,
            'payable_type' => $this->payableType,
            'metadata' => $this->metadata,
            'description' => $this->description,
        ];
    }
}
