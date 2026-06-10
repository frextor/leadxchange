<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
