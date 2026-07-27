<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale)
    {
        if (! in_array($locale, admin_locales(), true)) {
            return redirect($this->safeReturnPath($request->query('return')));
        }

        Session::put('locale', $locale);

        return redirect($this->safeReturnPath($request->query('return')))
            ->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }

    private function safeReturnPath(mixed $return): string
    {
        if (! is_string($return) || $return === '') {
            return '/';
        }

        if (! str_starts_with($return, '/') || str_starts_with($return, '//')) {
            return '/';
        }

        return $return;
    }
}
