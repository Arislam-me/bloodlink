<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\BloodGroup;
use App\Models\Division;
use App\Services\DonorProfileService;
use App\Services\PhoneVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'bloodGroups' => BloodGroup::options(),
            'divisions' => Division::options(),
        ]);
    }

    public function store(RegisterRequest $request, DonorProfileService $profiles, PhoneVerificationService $otp): RedirectResponse
    {
        $user = $profiles->register($request->validated(), $request->file('photo'));

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        $otp->send($user);

        return redirect()->route('phone.verify.notice')
            ->with('success', 'BloodLink-এ স্বাগতম! আপনার মোবাইলে ৬ সংখ্যার একটি কোড পাঠানো হয়েছে।');
    }
}
