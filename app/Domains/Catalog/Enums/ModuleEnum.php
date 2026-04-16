<?php

namespace App\Domains\Catalog\Enums;

/**
 * Enum for catalog module types.
 *
 * Used for gateway availability rules and item categorization.
 */
enum ModuleEnum: string
{
    case BOOKING = 'booking';
    case SUBSCRIPTION = 'subscription';
}
