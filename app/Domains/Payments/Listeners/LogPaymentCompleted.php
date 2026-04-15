<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Payments\Events\PaymentCompleted;
use Illuminate\Support\Facades\Log;

class LogPaymentCompleted
{
    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        Log::channel('payments')->info('Payment completed', [
            'transaction_id' => $event->transaction->id,
            'reference' => $event->transaction->reference,
            'gateway_id' => $event->transaction->gateway_id,
            'amount' => $event->transaction->amount,
        ]);
    }
}
