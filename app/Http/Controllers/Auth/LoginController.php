<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Bienvenue ' . Auth::user()->first_name . ' !');
        }

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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Vous avez été déconnecté avec succès.');
    }
}
