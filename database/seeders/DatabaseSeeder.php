<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Reference data is always seeded. Demo data only outside production
     * (and only when SEED_DEMO_DATA=true).
     */
    public function run(): void
    {
        $this->call([
            BloodGroupSeeder::class,
            LocationSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (! app()->isProduction() && config('bloodlink.seed_demo_data')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
