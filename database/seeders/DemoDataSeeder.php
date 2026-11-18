<?php

namespace Database\Seeders;

use App\Models\DonorProfile;
use App\Models\EmergencyRequest;
use App\Models\Upazila;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Synthetic demo data for LOCAL DEVELOPMENT ONLY. Refuses to run in production.
 * All demo users share the password "password" and use @example.* emails.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoDataSeeder refuses to run in production.');

            return;
        }

        // Concentrate some donors in Dhaka city so search demos are meaningful.
        $dhakaThanas = Upazila::with('district')->where('is_city_thana', true)
            ->whereHas('district', fn ($q) => $q->where('name', 'Dhaka'))->get();

        foreach ($dhakaThanas->random(min(12, $dhakaThanas->count())) as $thana) {
            DonorProfile::factory()->count(3)->inUpazila($thana)
                ->state(['user_id' => User::factory()->verifiedDonor()])->create();
        }

        DonorProfile::factory()->count(40)->create();

        EmergencyRequest::factory()->count(6)->create();
        EmergencyRequest::factory()->count(2)->pending()->create();

        $this->command?->info('Demo donors and requests created (password for all demo users: "password").');
    }
}
