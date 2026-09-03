<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class GeoBlock
{
    public function handle(Request $request, Closure $next): Response
    {
        // Charger la liste des pays bloqués
        $blockedRaw = SystemSetting::get('geo_blocked_countries', '');
        if (! $blockedRaw) {
            return $next($request);
        }
        $blocked = json_decode($blockedRaw, true) ?? [];
        if (empty($blocked)) {
            return $next($request);
        }

        // Sous-domaine admin → toujours exempt
        if (str_starts_with($request->getHost(), 'admin.')) {
            return $next($request);
        }

        // Admins et super_admins passent toujours
        $user = $request->user();
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // Routes admin toujours exemptées
        $path = ltrim($request->path(), '/');
        if ($path === 'admin' || str_starts_with($path, 'admin/') || $path === 'admin-access') {
            return $next($request);
        }

        // Détecter le pays de l'IP visiteur
        $ip   = $this->getRealIp($request);
        $info = $this->getGeoInfo($ip);

        if (! $info) {
            return $next($request); // En cas d'erreur API, on laisse passer
        }

        $countryCode = $info['countryCode'] ?? '';
        $countryName = $info['country']     ?? '';
        $cityName    = $info['city']        ?? '';

        // Blocage par pays
        if (in_array($countryCode, $blocked)) {
            return response()->view('geo-blocked', [
                'location' => $cityName ? "{$cityName}, {$countryName}" : $countryName,
                'reason'   => 'pays',
            ], 403);
        }

        // Blocage par ville
        $blockedCitiesRaw = SystemSetting::get('geo_blocked_cities', '');
        $blockedCities    = $blockedCitiesRaw ? (json_decode($blockedCitiesRaw, true) ?? []) : [];
        if ($cityName && ! empty($blockedCities)) {
            $cityLower = mb_strtolower($cityName);
            foreach ($blockedCities as $bc) {
                if (mb_strtolower($bc) === $cityLower) {
                    return response()->view('geo-blocked', [
                        'location' => $cityName,
                        'reason'   => 'ville',
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /** Récupère la vraie IP (gère proxies/Cloudflare) */
    private function getRealIp(Request $request): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'] as $header) {
            $val = $_SERVER[$header] ?? null;
            if ($val) return explode(',', $val)[0];
        }
        return $request->ip() ?? '127.0.0.1';
    }

    /** Géolocalise l'IP via ip-api.com (résultat mis en cache 6h) */
    private function getGeoInfo(string $ip): ?array
    {
        // IPs locales → pas de blocage
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return null;
        }

        $cacheKey = 'geoip_' . md5($ip);
        return Cache::remember($cacheKey, 21600, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=country,countryCode,regionName,city,status");
                if ($response->ok()) {
                    $data = $response->json();
                    return $data['status'] === 'success' ? $data : null;
                }
            } catch (\Throwable $e) {
                // Silencieux — on laisse passer en cas d'erreur
            }
            return null;
        });
    }
}
