<?php

namespace App\Policies;

use App\Domains\Booking\Models\Cart;
use App\Models\User;

class CartPolicy
{
    /**
     * Determine if the user can view the cart.
     */
    public function view(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    /**
     * Determine if the user can update the cart.
     */
    public function update(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    /**
     * Determine if the user can delete items from the cart.
     */
    public function delete(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }
}
