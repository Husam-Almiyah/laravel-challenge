<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Package;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
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
                'packages' => PackageResource::collection($packages),
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
                'package' => new PackageResource($package),
            ],
        ]);
    }
}
