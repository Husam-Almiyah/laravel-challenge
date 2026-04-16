<?php

namespace App\Domains\Account\Enums;

/**
 * Enum for user status values.
 *
 * Used for gateway availability rules and user account state management.
 */
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case VERIFIED = 'verified';
    case GUEST = 'guest';
}
