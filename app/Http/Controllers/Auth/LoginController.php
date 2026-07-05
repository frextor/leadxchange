<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LoginController (WEB - REFACTORED)
 * 
 * Thin controller for web interface.
 * Uses AuthService for business logic if needed.
 */
class LoginController extends Controller
{
    protected AuthService $authService;

    /**
     * Inject AuthService.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        // Validate
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'password.required' => 'Le mot de passe est requis.',
        ]);

        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Block admins from user area — they must use the admin domain
            if (in_array($user->role, ['admin', 'super_admin'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $adminDomain = env('ADMIN_DOMAIN', 'admin.leadxchange.test');
                return back()->withErrors([
                    'email' => "Ce compte est un compte administrateur. Connectez-vous sur http://{$adminDomain}/login",
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            ActivityLogger::log('auth.login', "Connexion réussie ({$user->email})", $user->id);

            // Create a Sanctum token for SPA API calls — revoke old one first
            $user->tokens()->where('name', 'web-spa')->delete();
            $token = $user->createToken('web-spa')->plainTextToken;
            $request->session()->put('web_api_token', $token);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Bienvenue ' . $user->first_name . ' !');
        }

        ActivityLogger::log('auth.login_failed', "Tentative de connexion échouée ({$credentials['email']})", null, null, ['email' => $credentials['email']]);

        // Failed login
        return back()->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        // Revoke the web-spa token before destroying the session
        if ($token = $request->session()->get('web_api_token')) {
            Auth::user()?->tokens()->where('name', 'web-spa')->delete();
        }

        $user = Auth::user();
        ActivityLogger::log('auth.logout', "Déconnexion ({$user?->email})", $user?->id);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Vous avez été déconnecté avec succès.');
    }
}
