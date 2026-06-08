<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SubscriptionPlan::create([
            'name' => 'Free',
            'price' => 0,
            'interval' => 'monthly',
            'max_branches' => 1,
            'lemon_squeezy_variant_id' => env('LS_VARIANT_FREE_MONTHLY'),
            'is_free' => true,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Basic Monthly',
            'price' => 30,
            'interval' => 'monthly',
            'max_branches' => 3,
            'is_free' => false,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Basic Yearly',
            'price' => 300,
            'interval' => 'yearly',
            'max_branches' => 3,
            'is_free' => false,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Pro Monthly',
            'price' => 65,
            'interval' => 'monthly',
            'max_branches' => 10,
            'is_free' => false,
        ]);

        \App\Models\SubscriptionPlan::create([
            'name' => 'Pro Yearly',
            'price' => 650,
            'interval' => 'yearly',
            'max_branches' => 10,
            'is_free' => false,
        ]);
    }
}
