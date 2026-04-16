<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Package;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * List all available packages.
     */
    public function index(Request $request): JsonResponse
    {
        $packages = Package::with(['services.category'])
            ->where('is_active', true)
            ->orderBy('price')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => [
                'packages' => $packages->getCollection()->map(fn ($package) => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'description' => $package->description,
                    'price' => $package->price,
                    'discount_percentage' => $package->discount_percentage,
                    'services_count' => $package->services->count(),
                    'services_preview' => $package->services->take(3)->map(fn ($service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->price,
                    ]),
                ]),
                'pagination' => [
                    'current_page' => $packages->currentPage(),
                    'per_page' => $packages->perPage(),
                    'total' => $packages->total(),
                    'last_page' => $packages->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Show a specific package with all its services.
     */
    public function show(string $slug): JsonResponse
    {
        $package = Package::with(['services.category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Package not found',
            ], 404);
        }

        $totalServicePrice = $package->services->sum('price');
        $savings = $totalServicePrice - $package->price;

        return response()->json([
            'success' => true,
            'data' => [
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'description' => $package->description,
                    'price' => $package->price,
                    'discount_percentage' => $package->discount_percentage,
                    'original_price' => $totalServicePrice,
                    'savings' => $savings,
                    'services' => $package->services->map(fn ($service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'slug' => $service->slug,
                        'description' => $service->description,
                        'price' => $service->price,
                        'category' => $service->category ? [
                            'id' => $service->category->id,
                            'name' => $service->category->name,
                        ] : null,
                    ]),
                ],
            ],
        ]);
    }
}
