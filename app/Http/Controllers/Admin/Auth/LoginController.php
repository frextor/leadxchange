<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()?->role === 'super_admin') {
            return redirect()->route('admin.super.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $this->checkRateLimit($request);

        $this->logAttempt($request);

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);
            ActivityLogger::log('admin.login_failed', "Tentative admin échouée ({$request->input('email')})", null, null, ['email' => $request->input('email')]);

            throw ValidationException::withMessages([
                'general' => 'Identifiants incorrects ou accès non autorisé.',
            ]);
        }

        if (Auth::user()->role !== 'super_admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);
            ActivityLogger::log('admin.login_failed', "Tentative admin non autorisée ({$request->input('email')})", null, null, ['email' => $request->input('email'), 'reason' => 'not_super_admin']);

            throw ValidationException::withMessages([
                'general' => 'Identifiants incorrects ou accès non autorisé.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();
        ActivityLogger::log('admin.login', "Connexion admin ({$request->input('email')})", Auth::id());

        // Stocker l'ID en attente de 2FA
        $request->session()->put('auth.2fa.user_id', Auth::id());
        Auth::logout();
        $request->session()->put('url.intended', route('admin.super.dashboard'));

        if (route_exists('admin.2fa.challenge')) {
            return redirect()->route('admin.2fa.challenge');
        }

        // 2FA pas encore implémenté → connexion directe
        Auth::loginUsingId($request->session()->pull('auth.2fa.user_id'), $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.super.dashboard'));
    }

    private function checkRateLimit(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'general' => "Trop de tentatives. Réessayez dans {$seconds} seconde(s).",
            ]);
        }
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', '')) . '|' . $request->ip();
    }

    private function logAttempt(Request $request): void
    {
        Log::channel('stack')->info('Admin login attempt', [
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'email'      => $request->input('email'),
            'at'         => now()->toDateTimeString(),
        ]);
    }
}
