<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Requires a verified mobile number (OTP) for privacy-sensitive features. */
class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->hasVerifiedPhone()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'আপনার মোবাইল নম্বর এখনো যাচাই হয়নি।'], 403)
                : redirect()->route('phone.verify.notice')->with('warning', 'চালিয়ে যেতে আপনার মোবাইল নম্বর যাচাই করুন।');
        }

        return $next($request);
    }
}
