<?php

namespace App\Domains\Payments\Data;

class PaymentResponse
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $transactionReference = null,
        public ?string $actionUrl = null,
        public ?string $message = null,
        public array $data = []
    ) {}

    /**
     * Named constructor for a successful response.
     */
    public static function successful(?string $reference = null, string $message = 'Success', array $data = []): self
    {
        return new self(
            success: true,
            status: 'success',
            transactionReference: $reference,
            message: $message,
            data: $data
        );
    }

    /**
     * Named constructor for a failed response.
     */
    public static function failed(string $message = 'Failed', array $data = []): self
    {
        return new self(
            success: false,
            status: 'failed',
            message: $message,
            data: $data
        );
    }

    /**
     * Named constructor for a redirect (e.g. 3D Secure).
     */
    public static function redirect(string $url, ?string $reference = null): self
    {
        return new self(
            success: true,
            status: 'redirect',
            transactionReference: $reference,
            actionUrl: $url
        );
    }
}
