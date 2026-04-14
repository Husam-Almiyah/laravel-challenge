<?php

namespace Database\Seeders;

use App\Domains\Catalog\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'priority' => 1,
                'services' => [
                    ['name' => 'Pipe Leak Repair', 'slug' => 'pipe-leak-repair', 'price' => 150, 'description' => 'Fixing leaking pipes and joints.'],
                    ['name' => 'Faucet Installation', 'slug' => 'faucet-installation', 'price' => 80, 'description' => 'Replacing or installing new faucets.'],
                ],
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'priority' => 2,
                'services' => [
                    ['name' => 'Outlet Repair', 'slug' => 'outlet-repair', 'price' => 50, 'description' => 'Fixing broken or short-circuited outlets.'],
                    ['name' => 'Light Fixture Installation', 'slug' => 'light-fixture-installation', 'price' => 120, 'description' => 'Installing ceiling lights or chandeliers.'],
                ],
            ],
        ];

        foreach ($categories as $catData) {
            $services = $catData['services'];
            unset($catData['services']);

            $category = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            foreach ($services as $service) {
                $category->services()->updateOrCreate(['slug' => $service['slug']], $service);
            }
        }
    }
}
