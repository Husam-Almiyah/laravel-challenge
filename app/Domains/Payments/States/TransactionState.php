<?php

namespace App\Domains\Payments\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class TransactionState extends State
{
    /**
     * Configure the state machine transitions.
     *
     * Flow: Pending -> Processing -> Paid/Failed
     *                            Paid -> Completed
     */
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            // From Pending
            ->allowTransition(Pending::class, Processing::class)
            ->allowTransition(Pending::class, Paid::class)
            ->allowTransition(Pending::class, Failed::class)
            // From Processing
            ->allowTransition(Processing::class, Paid::class)
            ->allowTransition(Processing::class, Failed::class)
            // From Paid
            ->allowTransition(Paid::class, Completed::class);
    }
}
