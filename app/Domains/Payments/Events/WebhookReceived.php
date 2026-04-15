<?php

namespace App\Domains\Payments\Events;

use App\Domains\Payments\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $gatewayName,
        public array $payload,
        public ?Transaction $transaction = null
    ) {}
}
