<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingItem;
use App\Domains\Booking\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a booking from the user's cart.
     *
     * @throws \Exception
     */
    public function createFromCart(User $user, array $data): Booking
    {
        $cart = Cart::with('items')
            ->where('user_id', $user->id)
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        $total = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

        return DB::transaction(function () use ($user, $cart, $total, $data) {
            // Create booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'address_id' => $data['address_id'],
                'scheduled_at' => $data['scheduled_at'],
                'total_amount' => $total,
                'status' => 'pending',
                'metadata' => [
                    'notes' => $data['notes'] ?? null,
                    'source' => 'cart',
                ],
            ]);

            // Create booking items from cart
            foreach ($cart->items as $cartItem) {
                BookingItem::create([
                    'booking_id' => $booking->id,
                    'itemable_id' => $cartItem->itemable_id,
                    'itemable_type' => $cartItem->itemable_type,
                    'name' => $cartItem->name,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                ]);
            }

            // Clear cart
            $cart->items()->delete();

            return $booking;
        });
    }
}
