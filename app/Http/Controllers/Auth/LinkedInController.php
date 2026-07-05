<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LinkedInController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * Initiate the LinkedIn OAuth flow from the web browser.
     * Sets a session flag so the callback knows this is a web request (not mobile).
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.linkedin.client_id');

        if (! $clientId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'La connexion LinkedIn n\'est pas encore configurée.']);
        }

        $state = Str::random(40);
        session([
            'linkedin_web'         => true,
            'linkedin_oauth_state' => $state,
        ]);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => $clientId,
            'redirect_uri'  => config('services.linkedin.redirect_uri'),
            'state'         => $state,
            'scope'         => 'openid profile email',
        ]);

        return redirect()->away('https://www.linkedin.com/oauth/v2/authorization?' . $query);
    }

    /**
     * Handle the OAuth callback.
     * - Web requests (have the linkedin_web session flag): complete the login.
     * - Mobile requests (no session flag): redirect to the app deep link as before.
     */
    public function callback(Request $request): RedirectResponse
    {
        // ── Mobile deep-link pass-through ─────────────────────────────────────
        if (! session('linkedin_web')) {
            $query = http_build_query($request->only(['code', 'state', 'error', 'error_description']));
            return redirect()->away('x-tensia://auth/linkedin/callback' . ($query !== '' ? "?{$query}" : ''));
        }

        // ── Web OAuth flow ────────────────────────────────────────────────────
        session()->forget('linkedin_web');

        // CSRF state check
        if ($request->state !== session()->pull('linkedin_oauth_state')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Erreur de sécurité. Veuillez réessayer.']);
        }

        if ($request->has('error')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Connexion LinkedIn annulée.']);
        }

        // Exchange authorization code for access token
        $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type'    => 'authorization_code',
            'code'          => $request->code,
            'redirect_uri'  => config('services.linkedin.redirect_uri'),
            'client_id'     => config('services.linkedin.client_id'),
            'client_secret' => config('services.linkedin.client_secret'),
        ]);

        if (! $tokenResponse->successful()) {
            Log::error('LinkedIn web token exchange failed', [
                'status' => $tokenResponse->status(),
                'body'   => $tokenResponse->json() ?: $tokenResponse->body(),
            ]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Connexion LinkedIn échouée. Veuillez réessayer.']);
        }

        $accessToken = $tokenResponse->json('access_token');

        // Fetch LinkedIn profile (OpenID Connect userinfo)
        $userInfoResponse = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://api.linkedin.com/v2/userinfo');

        if (! $userInfoResponse->successful()) {
            Log::error('LinkedIn web userinfo failed', [
                'status' => $userInfoResponse->status(),
            ]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Profil LinkedIn introuvable. Veuillez réessayer.']);
        }

        try {
            $user = $this->authService->loginWithLinkedIn($userInfoResponse->json());
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('login')
                ->withErrors(['email' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('LinkedIn web loginWithLinkedIn failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Erreur lors de la connexion LinkedIn. Veuillez réessayer.']);
        }

        // Establish web session
        Auth::login($user, true);
        $request->session()->regenerate();

        // Sanctum token for in-page API calls
        $user->tokens()->where('name', 'web-spa')->delete();
        $token = $user->createToken('web-spa')->plainTextToken;
        $request->session()->put('web_api_token', $token);

        ActivityLogger::log('auth.login', "Connexion LinkedIn ({$user->email})", $user->id);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ' . $user->first_name . ' !');
    }
}
