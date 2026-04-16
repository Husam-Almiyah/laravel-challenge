<?php

namespace App\Domains\Payments\Casts;

use App\Domains\Account\Enums\UserStatus;
use App\Domains\Catalog\Enums\ModuleEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Cast for payment gateway rules JSON with validation.
 *
 * Ensures rules follow the expected schema:
 * - cities: array of integers
 * - modules: array of strings
 * - required_status: string
 * - allowed_days: array of integers (0-6)
 * - min_amount: numeric
 */
class GatewayRulesCast implements CastsAttributes
{
    /**
     * Valid rule keys and their expected types.
     */
    protected const VALID_RULES = [
        'cities' => 'array',
        'modules' => 'array',
        'required_status' => 'string',
        'allowed_days' => 'array',
        'min_amount' => 'numeric',
    ];

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in gateway rules: '.json_last_error_msg());
        }

        return $decoded ?? [];
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value === null || $value === []) {
            return json_encode([]);
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException('Gateway rules must be an array');
        }

        // Validate rule keys
        foreach ($value as $ruleKey => $ruleValue) {
            if (! array_key_exists($ruleKey, self::VALID_RULES)) {
                throw new InvalidArgumentException("Invalid gateway rule key: {$ruleKey}");
            }

            $expectedType = self::VALID_RULES[$ruleKey];

            // Type validation
            if (! $this->isValidType($ruleValue, $expectedType)) {
                throw new InvalidArgumentException(
                    "Invalid type for gateway rule '{$ruleKey}'. Expected {$expectedType}"
                );
            }

            // Additional validation for specific rules
            $this->validateRuleValue($ruleKey, $ruleValue);
        }

        return json_encode($value);
    }

    /**
     * Check if a value matches the expected type.
     */
    protected function isValidType(mixed $value, string $type): bool
    {
        return match ($type) {
            'array' => is_array($value),
            'string' => is_string($value),
            'numeric' => is_numeric($value),
            default => false,
        };
    }

    /**
     * Validate specific rule values.
     */
    protected function validateRuleValue(string $key, mixed $value): void
    {
        match ($key) {
            'cities' => $this->validateIntegerArray($key, $value),
            'modules' => $this->validateModulesArray($key, $value),
            'required_status' => $this->validateUserStatus($key, $value),
            'allowed_days' => $this->validateDayOfWeekArray($key, $value),
            'min_amount' => $this->validateMinAmount($key, $value),
            default => null,
        };
    }

    /**
     * Validate an array of integers.
     */
    protected function validateIntegerArray(string $key, array $value): void
    {
        foreach ($value as $item) {
            if (! is_int($item)) {
                throw new InvalidArgumentException("Gateway rule '{$key}' must contain only integers");
            }
        }
    }

    /**
     * Validate an array of valid modules.
     */
    protected function validateModulesArray(string $key, array $value): void
    {
        $allowed = array_column(ModuleEnum::cases(), 'value');
        foreach ($value as $item) {
            if (! is_string($item) || ! in_array($item, $allowed)) {
                throw new InvalidArgumentException("Gateway rule '{$key}' must contain only allowed modules: ".implode(', ', $allowed));
            }
        }
    }

    /**
     * Validate user status.
     */
    protected function validateUserStatus(string $key, string $value): void
    {
        $allowed = array_column(UserStatus::cases(), 'value');
        if (! in_array($value, $allowed)) {
            throw new InvalidArgumentException("Gateway rule '{$key}' must be one of: ".implode(', ', $allowed));
        }
    }

    /**
     * Validate an array of strings.
     */
    protected function validateStringArray(string $key, array $value): void
    {
        foreach ($value as $item) {
            if (! is_string($item)) {
                throw new InvalidArgumentException("Gateway rule '{$key}' must contain only strings");
            }
        }
    }

    /**
     * Validate allowed days are valid day-of-week integers (0-6).
     */
    protected function validateDayOfWeekArray(string $key, array $value): void
    {
        foreach ($value as $day) {
            if (! is_int($day) || $day < 0 || $day > 6) {
                throw new InvalidArgumentException(
                    "Gateway rule '{$key}' must contain integers between 0 and 6 (day of week)"
                );
            }
        }
    }

    /**
     * Validate minimum amount is non-negative.
     */
    protected function validateMinAmount(string $key, mixed $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Gateway rule '{$key}' must be non-negative");
        }
    }
}
