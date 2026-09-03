<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    /** Routes/prefixes exemptés même en maintenance */
    protected array $except = [
        'admin',
        'admin-access',
        'login',
        'logout',
        '_debugbar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $enabled = SystemSetting::get('maintenance_banner_enabled', false);

        if (!$enabled) {
            return $next($request);
        }

        // Sous-domaine admin → toujours exempt
        $host = $request->getHost();
        if (str_starts_with($host, 'admin.')) {
            return $next($request);
        }

        // Admins et super_admins passent toujours
        $user = $request->user();
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // Exempter certains préfixes
        $path = ltrim($request->path(), '/');
        foreach ($this->except as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        // Tout le reste → page de maintenance
        $message = SystemSetting::get('maintenance_banner_message', 'Le site est temporairement en maintenance. Merci de réessayer dans quelques instants.');

        return response()->view('maintenance', ['message' => $message, 'adminLoginUrl' => url('/admin-access')], 503);
    }
}
