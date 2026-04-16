<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\BookingService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    /**
     * List user's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['items.itemable', 'address'])
            ->where('user_id', $request->user()->id)
            ->orderBy('scheduled_at', 'desc')
            ->paginate(min($request->get('per_page', 15), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'bookings' => BookingResource::collection($bookings->getCollection()),
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

        $this->authorize('view', $booking);

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
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'address_id' => [
                'required',
                Rule::exists('addresses', 'id')->where('user_id', $user->id),
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $booking = $this->bookingService->createFromCart($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => [
                    'booking' => new BookingResource($booking->load(['items', 'address'])),
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

        $this->authorize('cancel', $booking);

        $booking->update(['status' => BookingStatus::CANCELLED]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled',
        ]);
    }
}
