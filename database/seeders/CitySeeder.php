<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Riyadh', 'slug' => 'riyadh'],
            ['name' => 'Jeddah', 'slug' => 'jeddah'],
            ['name' => 'Mecca', 'slug' => 'mecca'],
            ['name' => 'Dammam', 'slug' => 'dammam'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(['slug' => $city['slug']], $city);
        }
    }
}
