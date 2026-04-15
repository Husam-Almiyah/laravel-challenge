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
                'name' => 'Basic Plan',
                'slug' => 'basic',
                'description' => 'Perfect for getting started',
                'price' => 49.00,
                'duration_days' => 30,
                'trial_days' => 7,
                'features' => ['5 bookings per month', 'Basic support', 'Email notifications'],
                'is_active' => true,
            ],
            [
                'name' => 'Standard Plan',
                'slug' => 'standard',
                'description' => 'Great for regular users',
                'price' => 99.00,
                'duration_days' => 30,
                'trial_days' => 14,
                'features' => ['Unlimited bookings', 'Priority support', 'SMS notifications', 'Custom reminders'],
                'is_active' => true,
            ],
            [
                'name' => 'Pro Plan',
                'slug' => 'pro',
                'description' => 'For power users',
                'price' => 199.00,
                'duration_days' => 30,
                'trial_days' => 14,
                'features' => ['Everything in Standard', 'Dedicated account manager', 'API access', 'Analytics dashboard'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
