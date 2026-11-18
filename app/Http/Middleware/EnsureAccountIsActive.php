<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Immediately signs out users whose account was suspended by an admin or
 * deactivated by themselves, on every request and every device.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== UserStatus::Active) {
            $message = $user->status === UserStatus::Suspended
                ? 'আপনার অ্যাকাউন্ট স্থগিত করা হয়েছে। ভুল মনে হলে আমাদের সাথে যোগাযোগ করুন।'
                : 'আপনার অ্যাকাউন্ট বন্ধ আছে। আবার লগইন করলে চালু হবে।';

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
