<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('bloodlink:create-admin')]
#[Description('Interactively create (or promote) a BloodLink administrator')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = strtolower(text('Admin email', required: true));
        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->forceFill(['role' => UserRole::Admin, 'status' => UserStatus::Active])->save();
            $this->info("{$email} is now an administrator.");

            return self::SUCCESS;
        }

        $name = text('Full name', required: true);
        $phone = PhoneNumber::normalize(text('Mobile number (01XXXXXXXXX)', required: true));
        $pass = password('Password', required: true);

        $v = Validator::make(compact('email', 'phone', 'pass'), [
            'email' => ['email', 'unique:users,email'],
            'phone' => ['regex:'.PhoneNumber::PATTERN, 'unique:users,phone'],
            'pass' => [Password::min(10)->letters()->mixedCase()->numbers()],
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'email' => $email, 'phone' => $phone, 'password' => $pass]);
        $user->forceFill([
            'role' => UserRole::Admin, 'status' => UserStatus::Active,
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'verified_at' => now(),
        ])->save();

        $this->info("Administrator {$email} created. Sign in at ".url('/admin'));

        return self::SUCCESS;
    }
}
