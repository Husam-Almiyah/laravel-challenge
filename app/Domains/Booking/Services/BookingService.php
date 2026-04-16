<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingItem;
use App\Domains\Booking\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Create a booking from user's cart.
     *
     * @throws \Exception
     */
    public function createFromCart(User $user, array $data): Booking
    {
        $cart = $this->validateAndGetCart($user);

        if (! $user->hasActiveSubscription()) {
            throw new \Exception('An active subscription is required to book maintenance services.');
        }

        $total = $this->calculateCartTotal($cart);

        return DB::transaction(function () use ($user, $data, $cart, $total) {
            try {
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
                $this->createBookingItems($booking, $cart);

                // Clear cart
                $cart->items()->delete();

                Log::info('Booking created from cart', [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'total' => $total,
                ]);

                return $booking;
            } catch (\Exception $e) {
                Log::error('Failed to create booking from cart', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Validate cart and return it.
     *
     * @throws \Exception
     */
    protected function validateAndGetCart(User $user): Cart
    {
        $cart = Cart::with('items')
            ->where('user_id', $user->id)
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        return $cart;
    }

    /**
     * Calculate total amount from cart items.
     */
    protected function calculateCartTotal(Cart $cart): float
    {
        return $cart->items->sum(fn ($item) => $item->price * $item->quantity);
    }

    /**
     * Create booking items from cart items.
     */
    protected function createBookingItems(Booking $booking, Cart $cart): void
    {
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
    }
}
