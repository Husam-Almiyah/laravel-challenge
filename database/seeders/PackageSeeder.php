<?php

namespace Database\Seeders;

use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some services to attach to packages
        $pipeRepair = Service::where('slug', 'pipe-leak-repair')->first();
        $faucetInstall = Service::where('slug', 'faucet-installation')->first();
        $outletRepair = Service::where('slug', 'outlet-repair')->first();
        $lightInstall = Service::where('slug', 'light-fixture-installation')->first();

        // Package 1: Full Home Maintenance
        $goldenBundle = Package::updateOrCreate(
            ['slug' => 'golden-maintenance-bundle'],
            [
                'name' => 'Golden Maintenance Bundle',
                'description' => 'A complete bundle covering plumbing and electrical essentials.',
                'price' => 250.00, // Discounted price
                'is_active' => true,
            ]
        );
        $goldenBundle->services()->sync([$pipeRepair->id, $outletRepair->id, $lightInstall->id]);

        // Package 2: Plumbing Plus
        $plumbingPlus = Package::updateOrCreate(
            ['slug' => 'plumbing-plus'],
            [
                'name' => 'Plumbing Plus',
                'description' => 'All your plumbing needs in one package.',
                'price' => 200.00,
                'is_active' => true,
            ]
        );
        $plumbingPlus->services()->sync([$pipeRepair->id, $faucetInstall->id]);
    }
}
