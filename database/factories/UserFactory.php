<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            // Bangladeshi mobile format: 01[3-9]XXXXXXXX
            'phone' => '01'.fake()->randomElement(['3', '4', '5', '6', '7', '8', '9']).fake()->unique()->numerify('########'),
            'phone_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        // role/status are guarded from mass assignment, so set them after making.
        // Model defaults already set role=user / status=active; states below override them.
        return $this;
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null, 'phone_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->afterMaking(fn (User $u) => $u->role = UserRole::Admin);
    }

    public function suspended(): static
    {
        return $this->afterMaking(fn (User $u) => $u->status = UserStatus::Suspended);
    }

    public function verifiedDonor(): static
    {
        return $this->afterMaking(fn (User $u) => $u->verified_at = now());
    }
}
