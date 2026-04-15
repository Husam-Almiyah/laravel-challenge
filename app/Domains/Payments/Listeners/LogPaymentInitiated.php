<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Payments\Events\PaymentInitiated;
use Illuminate\Support\Facades\Log;

class LogPaymentInitiated
{
    /**
     * Handle the event.
     */
    public function handle(PaymentInitiated $event): void
    {
        Log::channel('payments')->info('Payment initiated', [
            'transaction_id' => $event->transaction->id,
            'gateway_id' => $event->transaction->gateway_id,
            'amount' => $event->transaction->amount,
            'currency' => $event->transaction->currency,
            'payer_type' => $event->transaction->payer_type,
            'payable_type' => $event->transaction->payable_type,
        ]);
    }
}
