<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Payments\Events\PaymentSucceeded;
use Illuminate\Support\Facades\Log;

class LogPaymentSucceeded
{
    /**
     * Handle the event.
     */
    public function handle(PaymentSucceeded $event): void
    {
        Log::channel('payments')->info('Payment succeeded', [
            'transaction_id' => $event->transaction->id,
            'reference' => $event->transaction->reference,
            'gateway_id' => $event->transaction->gateway_id,
            'amount' => $event->transaction->amount,
            'currency' => $event->transaction->currency,
        ]);
    }
}
