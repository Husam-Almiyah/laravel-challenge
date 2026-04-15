<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Models\Cart;
use App\Domains\Booking\Models\CartItem;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get user's cart with items.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::with(['items.itemable' => function ($morphTo) {
            $morphTo->morphWith([
                Service::class => ['category'],
                Package::class => ['services'],
            ]);
        }])->firstOrCreate(['user_id' => $request->user()->id]);

        $total = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => new CartResource($cart),
            ],
        ]);
    }

    /**
     * Add item to cart.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // Check if item already exists in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('itemable_id', $request->itemable_id)
            ->where('itemable_type', $request->itemable_type)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $request->quantity);
            $item = $existingItem;
        } else {
            // Get the item details using explicit mapping for security
            $modelClass = match ($request->itemable_type) {
                Service::class => Service::class,
                Package::class => Package::class,
                default => throw new \InvalidArgumentException('Invalid itemable type'),
            };

            $itemable = $modelClass::findOrFail($request->itemable_id);

            $item = CartItem::create([
                'cart_id' => $cart->id,
                'itemable_id' => $itemable->id,
                'itemable_type' => $itemable::class,
                'name' => $itemable->name,
                'price' => $itemable->price,
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
    }

    /**
     * Update cart item quantity.
     */
    public function update(UpdateCartRequest $request, string $itemId): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
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
        $cart->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }
}
