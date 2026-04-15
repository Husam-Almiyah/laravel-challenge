<?php

namespace App\Domains\Payments\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'driver',
        'priority',
        'currency',
        'rules',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'rules' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the transactions processed by this gateway.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'gateway_id');
    }
}
