<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        $adminDomain = env('ADMIN_DOMAIN', 'admin.leadxchange.test');

        foreach ($guards as $guard) {
            if (!Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();

            // Admin / Super Admin — always go to the admin dashboard regardless of domain
            if (in_array($user->role, ['admin', 'super_admin'])) {
                return redirect()->route('admin.dashboard');
            }

            // Regular user on admin domain — they have no business here
            if ($request->getHost() === $adminDomain) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('admin.login')->withErrors([
                    'email' => 'Ce compte membre n\'a pas accès à l\'administration.',
                ]);
            }

            // Regular user on user domain — go to dashboard
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
