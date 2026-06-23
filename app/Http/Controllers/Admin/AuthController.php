<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            return $this->redirectAfterLogin();
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!auth()->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
                ->onlyInput('email');
        }

        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()
                ->withErrors(['email' => 'Accès refusé. Ce compte n\'a pas les droits administrateur.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectAfterLogin();
    }

    private function redirectAfterLogin(): RedirectResponse
    {
        return auth()->user()->isSuperAdmin()
            ? redirect()->route('admin.super.dashboard')
            : redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
