<?php

namespace App\Providers;

use App\Models\EmergencyRequest;
use App\Models\User;
use App\Services\Sms\HttpSmsSender;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\SmsSender;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsSender::class, function () {
            $cfg = config('bloodlink.sms');

            return $cfg['driver'] === 'http' && $cfg['api_url'] && $cfg['api_key']
                ? new HttpSmsSender($cfg['api_url'], $cfg['api_key'], $cfg['sender_id'])
                : new LogSmsSender;
        });
    }

    public function boot(): void
    {
        // Catch N+1 queries and silently-discarded attributes during development.
        Model::shouldBeStrict(! $this->app->isProduction());

        // Bangla month names and "২ ঘণ্টা আগে" style relative dates on the public site.
        Carbon::setLocale($this->app->getLocale());

        // Stable morph aliases instead of class names in the DB (reports.reportable_type).
        Relation::enforceMorphMap([
            'user' => User::class,
            'emergency_request' => EmergencyRequest::class,
        ]);

        // Generate https links in production and whenever the site is served over https
        // (e.g. through a Cloudflare tunnel), so assets aren't blocked as mixed content.
        if ($this->app->isProduction() || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(8)->letters()->mixedCase()->numbers()->uncompromised()
            : Password::min(8)->letters()->numbers());

        Gate::define('admin', fn (User $user) => $user->isAdmin());

        $this->configureRateLimiting();
    }

    /**
     * Named rate limiters used by route middleware ("throttle:<name>").
     * Keys combine user id and IP so limits hold across devices and sessions.
     */
    private function configureRateLimiting(): void
    {
        $byUserOrIp = fn (Request $r) => $r->user()?->id ? 'u:'.$r->user()->id : 'ip:'.$r->ip();

        // Public search: generous for humans, hostile to scrapers.
        RateLimiter::for('search', fn (Request $r) => [
            Limit::perMinute(30)->by($byUserOrIp($r)),
            Limit::perHour(300)->by('ip:'.$r->ip()),
        ]);

        // Unlocking donor contact profiles (on top of the monthly quota).
        // No monthly/daily cap on opening numbers; only a short burst guard against bots.
        RateLimiter::for('profile-unlock', fn (Request $r) => Limit::perMinute(20)->by($byUserOrIp($r)));

        RateLimiter::for('contact-request', fn (Request $r) => Limit::perMinute(3)->by($byUserOrIp($r)));
        RateLimiter::for('emergency', fn (Request $r) => Limit::perHour(5)->by($byUserOrIp($r)));
        RateLimiter::for('report', fn (Request $r) => Limit::perHour(10)->by($byUserOrIp($r)));
        RateLimiter::for('otp', fn (Request $r) => [
            Limit::perMinute(2)->by($byUserOrIp($r)),
            Limit::perHour(8)->by($byUserOrIp($r)),
        ]);
        RateLimiter::for('auth', fn (Request $r) => Limit::perMinute(10)->by('ip:'.$r->ip()));
        RateLimiter::for('locations', fn (Request $r) => Limit::perMinute(120)->by('ip:'.$r->ip()));
    }
}
