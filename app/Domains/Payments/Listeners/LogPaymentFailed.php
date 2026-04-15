<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Payments\Events\PaymentFailed;
use Illuminate\Support\Facades\Log;

class LogPaymentFailed
{
    /**
     * Handle the event.
     */
    public function handle(PaymentFailed $event): void
    {
        Log::channel('payments')->error('Payment failed', [
            'transaction_id' => $event->transaction->id,
            'gateway_id' => $event->transaction->gateway_id,
            'amount' => $event->transaction->amount,
            'currency' => $event->transaction->currency,
            'reason' => $event->reason,
            'error_details' => $event->transaction->metadata,
        ]);
    }
}
