<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\DataDeletionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Executes approved data deletion requests.
 *  - "account": permanently deletes the user; FK cascades remove profile, history,
 *    views, contact requests, consents and emergency requests.
 *  - "personal_data": removes donor/profile data and anonymizes the account,
 *    keeping only a non-identifying shell (so moderation records stay consistent).
 */
class DataDeletionService
{
    public function process(DataDeletionRequest $request, User $admin): void
    {
        DB::transaction(function () use ($request, $admin) {
            $user = User::withTrashed()->find($request->user_id);

            if ($user) {
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                if ($request->type === 'account') {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                    $user->notifications()->delete();
                    $user->forceDelete();
                } else {
                    $this->anonymize($user);
                }
            }

            $request->forceFill([
                'status' => 'completed',
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ])->save();
        });
    }

    private function anonymize(User $user): void
    {
        $user->donorProfile()->delete();
        $user->donations()->delete();
        $user->profileViews()->delete();
        $user->sentContactRequests()->delete();
        $user->receivedContactRequests()->delete();
        $user->notifications()->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->forceFill([
            'name' => 'Deleted User',
            'email' => "deleted-{$user->id}@deleted.invalid",
            'phone' => 'del-'.$user->id,
            'password' => Str::random(64),
            'profile_photo_path' => null,
            'email_verified_at' => null,
            'phone_verified_at' => null,
            'verified_at' => null,
            'status' => UserStatus::Deactivated,
            'status_reason' => 'Personal data deleted on request',
            'remember_token' => null,
        ])->save();
    }
}
