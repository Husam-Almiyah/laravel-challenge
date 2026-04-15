<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\Cart;
use App\Domains\Booking\Services\BookingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * List user's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['items.itemable'])
            ->where('user_id', $request->user()->id)
            ->orderBy('scheduled_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => BookingResource::collection($bookings),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Show a specific booking.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $booking = Booking::with(['items.itemable', 'address'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => new BookingResource($booking),
            ],
        ]);
    }

    /**
     * Create a booking from cart.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $bookingService = new BookingService;
            $booking = $bookingService->createFromCart($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => new BookingResource($booking),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel a booking.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->findOrFail($id);

        // Only allow cancellation of pending or confirmed bookings
        if (! in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled',
            ], 422);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled',
        ]);
    }
}
