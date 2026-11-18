<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $user = $request->user();

        if ($user->status === UserStatus::Suspended) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'login' => 'আপনার অ্যাকাউন্ট স্থগিত করা হয়েছে। সাহায্যের জন্য আমাদের সাথে যোগাযোগ করুন।',
            ]);
        }

        $flash = null;
        if ($user->status === UserStatus::Deactivated) {
            // Self-deactivated accounts are reactivated by signing in again.
            $user->forceFill(['status' => UserStatus::Active, 'status_reason' => null])->save();
            $flash = 'আবার স্বাগতম! আপনার অ্যাকাউন্ট আবার চালু হয়েছে।';
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', $flash ?? 'সফলভাবে লগইন হয়েছে।');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'আপনি লগ আউট করেছেন।');
    }
}
