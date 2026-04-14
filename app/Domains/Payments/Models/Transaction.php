<?php

namespace App\Domains\Payments\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'payer_id',
        'payer_type',
        'payable_id',
        'payable_type',
        'gateway_id',
        'amount',
        'currency',
        'status',
        'reference',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the payer entity.
     */
    public function payer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the payable entity (what was paid for).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the gateway used for this transaction.
     */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }
}
