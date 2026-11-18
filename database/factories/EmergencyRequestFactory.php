<?php

namespace Database\Factories;

use App\Enums\ContactMethod;
use App\Enums\EmergencyStatus;
use App\Models\BloodGroup;
use App\Models\EmergencyRequest;
use App\Models\Upazila;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmergencyRequest>
 */
class EmergencyRequestFactory extends Factory
{
    public function definition(): array
    {
        $upazila = Upazila::query()->with('district')->inRandomOrder()->first();
        $neededAt = now()->addHours(fake()->numberBetween(4, 72))->startOfHour();

        return [
            'user_id' => User::factory(),
            'blood_group_id' => BloodGroup::query()->inRandomOrder()->value('id'),
            'hospital_name' => fake()->randomElement(['Dhaka Medical College Hospital', 'Square Hospital', 'Chattogram Medical College Hospital', 'Upazila Health Complex']),
            'hospital_address' => fake()->streetAddress(),
            'division_id' => $upazila?->district->division_id,
            'district_id' => $upazila?->district_id,
            'upazila_id' => $upazila?->id,
            'units_needed' => fake()->numberBetween(1, 4),
            'needed_at' => $neededAt,
            'contact_method' => ContactMethod::InApp,
            'details' => fake()->sentence(12),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (EmergencyRequest $r) {
            $r->status ??= EmergencyStatus::Active;
            $r->expires_at ??= $r->needed_at->copy()->addHours(config('bloodlink.emergency_expiry_grace_hours'));
        });
    }

    public function pending(): static
    {
        return $this->afterMaking(fn (EmergencyRequest $r) => $r->status = EmergencyStatus::Pending);
    }
}
