<?php

namespace App\Services;

use App\Models\Area;
use App\Models\DonationHistory;
use App\Models\DonorProfile;
use App\Models\Upazila;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Write operations for donor profiles, location, photo and donation history.
 * Keeps donor_profiles.last_donation_date in sync with donation_histories.
 */
class DonorProfileService
{
    /**
     * Resolve the Area for an upazila from either a selected id or a typed name.
     * Typed names are auto-approved; admins can still rename or merge them.
     */
    public function resolveArea(int $upazilaId, ?int $areaId, ?string $areaName): ?int
    {
        if ($areaId) {
            return Area::where('id', $areaId)->where('upazila_id', $upazilaId)->value('id');
        }

        $name = Str::of((string) $areaName)->squish()->title()->limit(100, '')->toString();
        if ($name === '') {
            return null;
        }

        return Area::firstOrCreate(['upazila_id' => $upazilaId, 'name' => $name], ['is_approved' => true])->id;
    }

    /**
     * Derive division/district from the upazila so clients can never submit an
     * inconsistent location hierarchy.
     *
     * @return array{division_id:int, district_id:int, upazila_id:int}
     */
    public function locationFromUpazila(int $upazilaId): array
    {
        $upazila = Upazila::with('district')->findOrFail($upazilaId);

        return [
            'division_id' => $upazila->district->division_id,
            'district_id' => $upazila->district_id,
            'upazila_id' => $upazila->id,
        ];
    }

    /** @param array<string, mixed> $data validated registration data */
    public function register(array $data, ?UploadedFile $photo): User
    {
        return DB::transaction(function () use ($data, $photo) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
            ]);

            if ($photo) {
                $this->storePhoto($user, $photo);
            }

            $location = $this->locationFromUpazila((int) $data['upazila_id']);

            $profile = new DonorProfile([
                ...$location,
                'blood_group_id' => $data['blood_group_id'],
                'area_id' => $this->resolveArea($location['upazila_id'], null, $data['area_name'] ?? null),
                'last_donation_date' => $data['last_donation_date'] ?? null,
                'facebook_url' => $data['facebook_url'],
                'date_of_birth' => $data['date_of_birth'],
                'weight_kg' => $data['weight_kg'],
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
                // Every member is listed; availability is automatic (donation cooldown);
                // the phone number is shown to verified members who unlock the profile,
                // which the user consents to by accepting the terms at registration.
                'is_available' => true,
                'accepts_contact_requests' => true,
                'share_phone_on_unlock' => true,
                'is_listed' => true,
            ]);
            $user->donorProfile()->save($profile);

            if (! empty($data['last_donation_date'])) {
                $user->donations()->create([
                    'donated_on' => $data['last_donation_date'],
                    'notes' => 'নিবন্ধনের সময় দেওয়া তথ্য',
                ]);
            }

            UserConsent::record($user, UserConsent::TERMS, true);
            UserConsent::record($user, UserConsent::PRIVACY, true);
            UserConsent::record($user, UserConsent::PHONE_ON_UNLOCK, true);

            return $user;
        });
    }

    public function storePhoto(User $user, UploadedFile $photo): void
    {
        $old = $user->profile_photo_path;
        // Random file name: never derived from user input.
        $path = $photo->storeAs('profile-photos', Str::random(40).'.'.$photo->extension(), 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        if ($old) {
            Storage::disk('public')->delete($old);
        }
    }

    public function removePhoto(User $user): void
    {
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->forceFill(['profile_photo_path' => null])->save();
        }
    }

    public function addDonation(User $user, array $data): DonationHistory
    {
        return DB::transaction(function () use ($user, $data) {
            $donation = $user->donations()->create($data);
            $this->syncLastDonation($user);

            return $donation;
        });
    }

    public function deleteDonation(DonationHistory $donation): void
    {
        DB::transaction(function () use ($donation) {
            $user = $donation->user;
            $donation->delete();
            $this->syncLastDonation($user);
        });
    }

    /** last_donation_date = most recent donation in history (or null). */
    public function syncLastDonation(User $user): void
    {
        $latest = DonationHistory::where('user_id', $user->id)->max('donated_on');
        $user->donorProfile?->forceFill(['last_donation_date' => $latest])->save();
    }
}
