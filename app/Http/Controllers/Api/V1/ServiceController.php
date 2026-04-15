<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Service;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * List all available services.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::with('category')
            ->where('is_active', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search by name (sanitized)
        if ($request->has('search')) {
            $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            $query->where('name', 'like', '%'.$searchTerm.'%');
        }

        $services = $query
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'services' => ServiceResource::collection($services),
                'pagination' => [
                    'current_page' => $services->currentPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'last_page' => $services->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Show a specific service.
     */
    public function show(string $slug): JsonResponse
    {
        $service = Service::with('category')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service' => new ServiceResource($service),
            ],
        ]);
    }
}
