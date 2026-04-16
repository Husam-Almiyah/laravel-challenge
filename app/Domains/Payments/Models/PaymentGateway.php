<?php

namespace App\Domains\Payments\Models;

use App\Domains\Payments\Casts\GatewayRulesCast;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
        'rules' => GatewayRulesCast::class,
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Invalidate gateway availability cache when gateway is updated
        // Increment cache version to invalidate all cached gateway availability queries
        static::updated(function ($gateway) {
            Cache::increment('gateways_availability_version');
        });

        static::created(function ($gateway) {
            Cache::increment('gateways_availability_version');
        });

        static::deleted(function ($gateway) {
            Cache::increment('gateways_availability_version');
        });
    }

    /**
     * Get the transactions processed by this gateway.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'gateway_id');
    }
}
