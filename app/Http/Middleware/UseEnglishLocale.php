<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The public site is Bangla; the Filament admin panel stays in English. */
class UseEnglishLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('en');
        Carbon::setLocale('en');

        return $next($request);
    }
}
