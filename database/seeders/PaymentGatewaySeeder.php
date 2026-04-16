<?php

namespace Database\Seeders;

use App\Domains\Payments\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'mada',
                'driver' => 'mada',
                'priority' => 1,
                'currency' => 'SAR',
                'rules' => [
                    'cities' => [1], // Only Riyadh
                    'modules' => ['booking'], // Only for bookings
                ],
                'is_active' => true,
            ],
            [
                'name' => 'apple_pay',
                'driver' => 'applePay',
                'priority' => 2,
                'currency' => 'SAR',
                'rules' => [
                    'cities' => [1, 2], // Riyadh and Jeddah
                    'required_status' => 'verified', // Premium/Verified users only
                ],
                'is_active' => true,
            ],
            [
                'name' => 'stripe',
                'driver' => 'stripe',
                'priority' => 3,
                'currency' => 'SAR',
                'rules' => [
                    'min_amount' => 10,
                    'allowed_days' => [0, 1, 2, 3, 4], // Sunday to Thursday only (Example Business Rule)
                ],
                'is_active' => true,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(['name' => $gateway['name']], $gateway);
        }
    }
}
