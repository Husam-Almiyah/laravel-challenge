<?php

namespace Database\Seeders;

use App\Domains\Subscriptions\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'price' => 0,
                'duration_days' => 7,
                'is_trial' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Standard Monthly',
                'slug' => 'standard-monthly',
                'price' => 99.00,
                'duration_days' => 30,
                'is_trial' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pro Yearly',
                'slug' => 'pro-yearly',
                'price' => 999.00,
                'duration_days' => 365,
                'is_trial' => false,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
