<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = admin_locales();
        $locale = Session::get('locale', $request->cookie('locale'));

        if (is_string($locale) && in_array($locale, $allowed, true)) {
            App::setLocale($locale);

            if (! Session::has('locale')) {
                Session::put('locale', $locale);
            }
        } elseif ($default = config('admin.default_locale')) {
            App::setLocale($default);
        }

        return $next($request);
    }
}
