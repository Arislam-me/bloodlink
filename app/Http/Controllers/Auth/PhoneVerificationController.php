<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneVerificationController extends Controller
{
    public function __construct(private readonly PhoneVerificationService $otp) {}

    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedPhone()
            ? redirect()->route('dashboard')
            : view('auth.verify-phone', ['user' => $request->user()]);
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedPhone()) {
            return redirect()->route('dashboard');
        }

        $wait = $this->otp->send($request->user());

        return $wait > 0
            ? back()->with('warning', "নতুন কোড চাইতে {$wait} সেকেন্ড অপেক্ষা করুন।")
            : back()->with('success', 'আপনার মোবাইলে নতুন কোড পাঠানো হয়েছে।');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:'.config('bloodlink.otp.length')]]);

        $this->otp->verify($request->user(), $request->string('code'));

        return redirect()->intended(route('dashboard'))->with('success', 'আপনার মোবাইল নম্বর যাচাই হয়েছে।');
    }
}
