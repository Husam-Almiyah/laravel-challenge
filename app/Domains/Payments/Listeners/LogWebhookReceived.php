<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Payments\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

class LogWebhookReceived
{
    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        Log::channel('payments')->info('Webhook received', [
            'gateway' => $event->gatewayName,
            'transaction_id' => $event->transaction?->id,
            'event_type' => $event->payload['type'] ?? null,
            'payload_summary' => array_keys($event->payload),
        ]);
    }
}
