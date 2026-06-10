<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        if (in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Ce compte administrateur ne peut pas accéder à l\'espace membre. Utilisez ' . env('ADMIN_DOMAIN', 'admin.leadxchange.test') . '/login.',
            ]);
        }

        return $next($request);
    }
}
