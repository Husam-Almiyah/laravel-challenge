<?php

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('pivot records are removed when package is soft deleted', function () {
    $category = Category::create(['name' => 'Test Cat', 'slug' => 'test-cat']);

    // Create a service and a package
    $service = Service::create([
        'category_id' => $category->id,
        'name' => 'Test Service',
        'slug' => 'test-service',
        'description' => 'Test Description',
        'price' => 100,
        'is_active' => true,
    ]);

    $package = Package::create([
        'category_id' => $category->id,
        'name' => 'Test Package',
        'slug' => 'test-package',
        'description' => 'Test Description',
        'price' => 500,
        'is_active' => true,
    ]);

    // Attach service to package
    $package->services()->attach($service->id);

    // Verify pivot exists
    expect(DB::table('package_service')->count())->toBe(1);

    // Soft delete package
    $package->delete();

    // Verify pivot is removed (handled by our booted hook)
    expect(DB::table('package_service')->count())->toBe(0);

    // Verify package still exists in DB (soft deleted)
    expect(Package::withTrashed()->count())->toBe(1);
    expect(Package::count())->toBe(0);
});

test('pivot records are removed when package is force deleted', function () {
    $category = Category::create(['name' => 'Test Cat 2', 'slug' => 'test-cat-2']);

    $service = Service::create([
        'category_id' => $category->id,
        'name' => 'Test Service 2',
        'slug' => 'test-service-2',
        'price' => 100,
    ]);

    $package = Package::create([
        'category_id' => $category->id,
        'name' => 'Test Package 2',
        'slug' => 'test-package-2',
        'price' => 500,
    ]);

    $package->services()->attach($service->id);
    expect(DB::table('package_service')->count())->toBe(1);

    // Force delete
    $package->forceDelete();

    expect(DB::table('package_service')->count())->toBe(0);
    expect(Package::withTrashed()->count())->toBe(0);
});
