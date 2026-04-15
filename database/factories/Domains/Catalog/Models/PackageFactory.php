<?php

namespace Database\Factories\Domains\Catalog\Models;

use App\Domains\Catalog\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' Package',
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 1000),
            'discount_percentage' => fake()->numberBetween(5, 30),
            'is_active' => true,
        ];
    }
}
