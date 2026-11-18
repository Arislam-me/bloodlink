<?php

namespace Database\Factories;

use App\Models\BloodGroup;
use App\Models\DonorProfile;
use App\Models\Upazila;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonorProfile>
 */
class DonorProfileFactory extends Factory
{
    public function definition(): array
    {
        $upazila = Upazila::query()->with('district')->inRandomOrder()->first();

        return [
            'user_id' => User::factory(),
            'blood_group_id' => BloodGroup::query()->inRandomOrder()->value('id'),
            'division_id' => $upazila?->district->division_id,
            'district_id' => $upazila?->district_id,
            'upazila_id' => $upazila?->id,
            'area_id' => null,
            'last_donation_date' => fake()->optional(0.7)->dateTimeBetween('-1 year', '-1 week'),
            'is_available' => fake()->boolean(75),
            'accepts_contact_requests' => true,
            'share_phone_on_unlock' => fake()->boolean(30),
            'is_listed' => true,
            'facebook_url' => 'https://www.facebook.com/'.fake()->unique()->userName(),
            'whatsapp_number' => null,
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'weight_kg' => fake()->numberBetween(50, 90),
        ];
    }

    public function inUpazila(Upazila $upazila): static
    {
        return $this->state(fn () => [
            'division_id' => $upazila->district->division_id,
            'district_id' => $upazila->district_id,
            'upazila_id' => $upazila->id,
        ]);
    }
}
