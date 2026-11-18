<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\BloodGroup;
use App\Models\DataDeletionRequest;
use App\Models\Division;
use App\Models\DonorProfile;
use App\Services\DonorProfileService;
use App\Services\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly DonorProfileService $profiles) {}

    public function edit(Request $request): View
    {
        $user = $request->user()->load('donorProfile.area');

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->donorProfile,
            'bloodGroups' => BloodGroup::options(),
            'divisions' => Division::options(),
            'pendingDeletion' => DataDeletionRequest::where('user_id', $user->id)->where('status', 'pending')->first(),
        ]);
    }

    /** Personal details + donor location. Changing the phone number requires verifying it again. */
    public function update(ProfileUpdateRequest $request, PhoneVerificationService $otp): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            $user->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']]);
            if ($user->isDirty('phone')) {
                $user->phone_verified_at = null;
            }
            $user->save();

            $location = $this->profiles->locationFromUpazila((int) $data['upazila_id']);
            $profileData = [
                ...$location,
                'blood_group_id' => $data['blood_group_id'],
                'area_id' => $this->profiles->resolveArea($location['upazila_id'], null, $data['area_name'] ?? null),
                'bio' => $data['bio'] ?? null,
                'facebook_url' => $data['facebook_url'],
                'date_of_birth' => $data['date_of_birth'],
                'weight_kg' => $data['weight_kg'],
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
            ];

            $user->donorProfile
                ? $user->donorProfile->fill($profileData)->save()
                : $user->donorProfile()->save(new DonorProfile($profileData));
        });

        if ($user->wasChanged('phone')) {
            $otp->send($user);

            return redirect()->route('phone.verify.notice')->with('warning', 'নতুন মোবাইল নম্বরটি যাচাই করুন। কোড পাঠানো হয়েছে।');
        }

        return back()->with('success', 'প্রোফাইল আপডেট হয়েছে।');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'],
        ]);
        $this->profiles->storePhoto($request->user(), $request->file('photo'));

        return back()->with('success', 'প্রোফাইল ছবি আপডেট হয়েছে।');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $this->profiles->removePhoto($request->user());

        return back()->with('success', 'প্রোফাইল ছবি সরানো হয়েছে।');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults(), 'different:current_password'],
        ]);

        $request->user()->forceFill(['password' => $request->string('password')])->save();
        // Sign out other devices after a password change.
        Auth::logoutOtherDevices($request->string('password'));

        return back()->with('success', 'পাসওয়ার্ড বদলানো হয়েছে। অন্য সব ডিভাইস থেকে লগ আউট করা হয়েছে।');
    }

    /** Self-service deactivation: hidden from search, can reactivate by signing in. */
    public function deactivate(Request $request): RedirectResponse
    {
        $request->validateWithBag('deactivate', ['password' => ['required', 'current_password']]);

        $user = $request->user();
        $user->forceFill(['status' => UserStatus::Deactivated, 'status_reason' => 'Self-deactivated'])->save();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'আপনার অ্যাকাউন্ট বন্ধ করা হয়েছে। আবার লগইন করলেই চালু হয়ে যাবে।');
    }

    /**
     * Request deletion of the account or personal data. An administrator processes
     * the request (see Filament > Data deletion requests); the profile is hidden immediately.
     */
    public function requestDeletion(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('deletion', [
            'type' => ['required', 'in:account,personal_data'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (DataDeletionRequest::where('user_id', $user->id)->where('status', 'pending')->exists()) {
            return back()->with('warning', 'আপনার একটি মুছে ফেলার অনুরোধ ইতিমধ্যে অপেক্ষায় আছে।');
        }

        DataDeletionRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $data['type'],
            'reason' => $data['reason'] ?? null,
        ]);

        $user->donorProfile?->forceFill(['is_listed' => false, 'accepts_contact_requests' => false])->save();

        return back()->with('success', 'আপনার অনুরোধ পেয়েছি। আপনার প্রোফাইল এখন লুকানো আছে, ৭ দিনের মধ্যে কাজটি সম্পন্ন করা হবে।');
    }
}
