<?php

namespace App\Domains\Payments\Services;

use App\Domains\Account\Enums\UserStatus;
use App\Domains\Payments\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GatewayAvailabilityResolver
{
    /**
     * Determine if a gateway is available for the given context.
     *
     * @param  array  $context  ['user' => User, 'city_id' => int, 'amount' => float, 'module' => string]
     */
    public function isAvailable(PaymentGateway $gateway, array $context): bool
    {
        if (! $gateway->is_active) {
            return false;
        }

        $rules = $gateway->rules ?? [];

        // 1. Check City Availability
        if (isset($rules['cities']) && ! empty($rules['cities'])) {
            $contextUser = $context['user'] ?? null;
            $currentCityId = null;

            if ($contextUser) {
                // Get from default address or any address
                $defaultAddress = $contextUser->addresses()->where('is_default', true)->first()
                                ?? $contextUser->addresses()->first();
                $currentCityId = $defaultAddress?->city_id ?? $context['city_id'] ?? null;
            } else {
                $currentCityId = $context['city_id'] ?? null;
            }

            if (! $currentCityId || ! in_array($currentCityId, $rules['cities'])) {
                return false;
            }
        }

        // 2. Check Module Availability (e.g. only for subscriptions)
        if (! empty($rules['modules'])) {
            $allowedModules = $rules['modules'];
            $contextModules = (array) ($context['modules'] ?? []);

            // If any requested modules are not in the gateway's allowed list, deny availability.
            if (collect($contextModules)->diff($allowedModules)->isNotEmpty()) {
                return false;
            }
        }

        // 3. User Status / Configuration Rule
        if (isset($rules['required_status'])) {
            $userStatus = $context['user_status'] ?? UserStatus::GUEST->value;
            if ($userStatus !== $rules['required_status']) {
                return false;
            }
        }

        // 4. Example Business Rule: Time-based (e.g. weekends only or specific hours)
        if (isset($rules['allowed_days']) && ! empty($rules['allowed_days'])) {
            $currentDay = $context['day_of_week'] ?? now()->dayOfWeek; // 0 (Sun) to 6 (Sat)
            if (! in_array($currentDay, $rules['allowed_days'])) {
                return false;
            }
        }

        // 5. Minimum Amount
        if (isset($rules['min_amount'])) {
            $amount = $context['amount'] ?? 0;
            if ($amount < $rules['min_amount']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all available gateways for the given context.
     */
    public function getAvailableGateways(array $context): Collection
    {
        // Cache key based on context (excluding large objects like User, use ID instead)
        $cacheContext = $context;
        if (isset($cacheContext['user']) && $cacheContext['user'] instanceof User) {
            $cacheContext['user_id'] = $cacheContext['user']->id;
            unset($cacheContext['user']);
        }

        // Use cache version to invalidate all gateway caches when gateways change
        $cacheVersion = Cache::get('gateways_availability_version', 'v1');
        $cacheKey = 'gateways_availability_'.$cacheVersion.'_'.md5(json_encode($cacheContext));

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($context) {
            return PaymentGateway::where('is_active', true)
                ->orderBy('priority')
                ->get()
                ->filter(fn ($gateway) => $this->isAvailable($gateway, $context));
        });
    }
}
