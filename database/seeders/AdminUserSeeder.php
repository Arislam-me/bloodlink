<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the first administrator from ADMIN_EMAIL / ADMIN_PHONE / ADMIN_PASSWORD.
 * If ADMIN_PASSWORD is empty a random one is generated and printed once.
 * For production prefer: php artisan bloodlink:create-admin
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('bloodlink.admin');
        if (! $cfg['email'] || User::withTrashed()->where('email', $cfg['email'])->exists()) {
            return;
        }

        $password = $cfg['password'] ?: Str::password(16);

        $admin = new User([
            'name' => $cfg['name'],
            'email' => $cfg['email'],
            'phone' => $cfg['phone'],
            'password' => $password,
        ]);
        $admin->role = UserRole::Admin;
        $admin->status = UserStatus::Active;
        $admin->email_verified_at = now();
        $admin->phone_verified_at = now();
        $admin->verified_at = now();
        $admin->save();

        if (! $cfg['password']) {
            $this->command?->warn("Admin created: {$cfg['email']} / generated password: {$password}  (change it after first login)");
        }
    }
}
