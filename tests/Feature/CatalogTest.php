<?php

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Service Tests
|--------------------------------------------------------------------------
*/

test('can browse services', function () {
    $category = Category::factory()->create();
    Service::factory()->count(5)->create([
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog/services');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'services' => [
                    '*' => ['id', 'name', 'slug', 'description', 'price', 'category'],
                ],
                'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
            ],
        ]);
});

test('can filter services by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Service::factory()->count(3)->create([
        'category_id' => $category1->id,
        'is_active' => true,
    ]);

    Service::factory()->count(2)->create([
        'category_id' => $category2->id,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog/services?category_id='.$category1->id);

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data.services');
});

test('can search services', function () {
    Service::factory()->create([
        'name' => 'Premium Haircut',
        'is_active' => true,
    ]);

    Service::factory()->create([
        'name' => 'Basic Haircut',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog/services?search=Premium');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data.services');
});

test('can view service details', function () {
    $category = Category::factory()->create();
    $service = Service::factory()->create([
        'slug' => 'premium-haircut',
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog/services/premium-haircut');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'slug' => 'premium-haircut',
                ],
            ],
        ]);
});

test('cannot view inactive service', function () {
    $service = Service::factory()->create([
        'slug' => 'inactive-service',
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/v1/catalog/services/inactive-service');

    $response->assertStatus(404);
});

/*
|--------------------------------------------------------------------------
| Package Tests
|--------------------------------------------------------------------------
*/

test('can browse packages', function () {
    Package::factory()->count(3)->create([
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog/packages');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'packages' => [
                    '*' => ['id', 'name', 'slug', 'description', 'price', 'discount_percentage', 'services_count'],
                ],
                'pagination' => ['current_page', 'per_page', 'total', 'last_page'],
            ],
        ]);
});

test('can view package details', function () {
    $package = Package::factory()->create([
        'slug' => 'test-package',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/catalog/packages/test-package');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                ],
            ],
        ])
        ->assertJsonStructure([
            'data' => [
                'package' => [
                    'id', 'name', 'slug', 'description', 'price',
                    'discount_percentage', 'original_price', 'savings', 'services',
                ],
            ],
        ]);
});

test('package shows savings calculation', function () {
    $package = Package::factory()->create([
        'slug' => 'savings-package',
        'price' => 100,
        'is_active' => true,
    ]);

    $service = Service::factory()->create(['price' => 50]);
    $package->services()->attach($service->id);
    $service2 = Service::factory()->create(['price' => 75]);
    $package->services()->attach($service2->id);

    $response = $this->getJson('/api/v1/catalog/packages/savings-package');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'package' => [
                    'original_price' => 125, // 50 + 75
                    'savings' => 25, // 125 - 100
                ],
            ],
        ]);
});
