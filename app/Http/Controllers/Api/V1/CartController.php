<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Models\Cart;
use App\Domains\Booking\Models\CartItem;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get user's cart with items.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::with(['items.itemable.category', 'items.itemable.services'])
            ->firstOrCreate(['user_id' => $request->user()->id]);

        $total = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => [
                    'id' => $cart->id,
                    'items' => $cart->items->map(fn ($item) => [
                        'id' => $item->id,
                        'itemable_type' => $item->itemable_type,
                        'name' => $item->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->price * $item->quantity,
                        'itemable' => $item->itemable ? [
                            'id' => $item->itemable->id,
                            'name' => $item->itemable->name,
                            'type' => class_basename($item->itemable),
                            'category' => $item->itemable->category?->name ?? null,
                            'services_count' => $item->itemable instanceof Package
                                ? $item->itemable->services->count()
                                : null,
                        ] : null,
                    ]),
                    'total' => $total,
                    'items_count' => $cart->items->count(),
                ],
            ],
        ]);
    }

    /**
     * Add item to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'itemable_id' => 'required|ulid',
            'itemable_type' => 'required|in:'.Service::class.','.Package::class,
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        return DB::transaction(function () use ($request) {
            // Use lockForUpdate to prevent race conditions
            $cart = Cart::where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $request->user()->id]);

            // Get the item details
            $itemable = $request->itemable_type::findOrFail($request->itemable_id);

            // Calculate price (apply discount for packages)
            $price = $this->calculateItemPrice($itemable);

            // Use updateOrCreate to handle race conditions with unique constraint
            $item = CartItem::where('cart_id', $cart->id)
                ->where('itemable_id', $itemable->id)
                ->where('itemable_type', $itemable::class)
                ->lockForUpdate()
                ->first();

            if ($item) {
                // Update existing item
                $item->increment('quantity', $request->quantity);
            } else {
                // Create new item
                $item = CartItem::create([
                    'cart_id' => $cart->id,
                    'itemable_id' => $itemable->id,
                    'itemable_type' => $itemable::class,
                    'name' => $itemable->name,
                    'price' => $price,
                    'quantity' => $request->quantity,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->price * $item->quantity,
                    ],
                ],
            ], 201);
        });
    }

    /**
     * Calculate item price with discount if applicable.
     */
    protected function calculateItemPrice($itemable): float
    {
        // Apply discount for packages
        if ($itemable instanceof Package && $itemable->discount_percentage > 0) {
            return $itemable->price * (1 - $itemable->discount_percentage / 100);
        }

        return $itemable->price;
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, string $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        $this->authorize('update', $cart);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => [
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->price * $item->quantity,
                ],
            ],
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, string $itemId): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        $this->authorize('update', $cart);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();

        $this->authorize('update', $cart);

        $cart->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }
}
