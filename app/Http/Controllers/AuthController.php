<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login()
    {
        return Inertia::render('Admin/Login', [
            'copy' => [
                'header' => __('content.login_header'),
                'description' => __('content.login_description'),
                'email' => __('content.email'),
                'password' => __('content.password'),
                'remember_me' => __('content.remember_me'),
                'login' => __('content.login'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['integer', 'min:0', 'max:1'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? 0))) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user?->isAdmin()) {
            Auth::logout();

            return back()->withErrors([
                'email' => __('auth.failed'),
            ]);
        }

        return redirect()->intended(admin_home_for($user));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
