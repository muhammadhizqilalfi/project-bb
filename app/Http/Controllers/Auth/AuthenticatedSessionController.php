<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        // Validation
        $credentials = $request->validate([
            'nip'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $request->input('nip') . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);
            return back()->withErrors([
                'nip' => "Terlalu banyak percobaan login. Silakan coba lagi dalam $minutes menit.",
            ])->onlyInput('nip');
        }

        // Auth
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            rateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            // Direct user
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 5 * 60);

        // If authentication fails, redirect back with an error message
        return back()->withErrors([
            'nip' => 'NIP atau password yang Anda masukkan salah.',
        ])->onlyInput('nip');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}