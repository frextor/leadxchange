<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Event;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Nationality;
use App\Models\PermissionDefinition;
use App\Models\Plan;
use App\Models\Market;
use App\Models\Sector;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * GET /api/settings
     *
     * Returns all reference data in a single call.
     * Designed for mobile app bootstrap (load once, cache locally).
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'version'           => 'v2',
            'event_categories'  => collect(Event::categoryLabels())
                                       ->map(fn($label, $key) => ['key' => $key, 'label' => $label])
                                       ->values(),
            'interests'     => Interest::orderBy('name')
                                   ->get(['id', 'name', 'icon']),

            'sectors'       => Sector::orderBy('name')
                                   ->get(['id', 'name']),

            'markets'       => Market::orderBy('name')
                                   ->get(['id', 'name']),

            'languages'     => Language::orderBy('name')
                                   ->get(['id', 'name', 'native_name', 'code']),

            'nationalities' => Nationality::orderBy('country')
                                   ->get(['id', 'name', 'country', 'code', 'flag']),

            'countries'     => Country::orderBy('name')
                                   ->get(['id', 'name', 'code', 'flag']),

            'plans'         => Plan::where('is_active', true)
                                   ->orderBy('sort_order')
                                   ->get(['id', 'name', 'label', 'description', 'price', 'annual_price', 'billing_period', 'max_leads', 'max_groups', 'max_users', 'features', 'permissions'])
                                   ->map(fn($plan) => [
                                       'id'             => $plan->id,
                                       'name'           => $plan->name,
                                       'label'          => $plan->label,
                                       'description'    => $plan->description,
                                       'price'          => $plan->price,
                                       'annual_price'   => $plan->annual_price,
                                       'billing_period' => $plan->billing_period,
                                       'max_leads'      => $plan->max_leads,
                                       'max_groups'     => $plan->max_groups,
                                       'max_users'      => $plan->max_users,
                                       'features'       => $this->planFeaturesToArray($plan->features),
                                       'permissions'    => $this->planPermissionsToArray($plan->permissions),
                                       'raw_permissions' => is_array($plan->permissions) ? $plan->permissions : (object)[],
                                       'is_visible'     => (bool) $plan->is_visible,
                                   ]),

            'cities'        => City::where('is_active', true)->with('country:id,name,code,flag')
                                   ->orderByRaw("CASE WHEN country_id = (SELECT id FROM countries WHERE code = 'MA') THEN 0 ELSE 1 END")
                                   ->orderBy('name')
                                   ->get(['id', 'name', 'country_id'])
                                   ->map(fn($city) => [
                                       'id'           => $city->id,
                                       'name'         => $city->name,
                                       'country_id'   => $city->country_id,
                                       'country_name' => $city->country?->name,
                                       'country_code' => $city->country?->code,
                                       'flag'         => $city->country?->flag,
                                   ]),
        ]);
    }

    private function planFeaturesToArray(mixed $features): array
    {
        if (empty($features)) return [];

        // Already a flat string array — return as-is
        if (array_is_list($features) && isset($features[0]) && is_string($features[0])) {
            return $features;
        }

        // Associative object — convert to display strings
        $result = [];
        $maxConn = $features['max_connections_per_month'] ?? null;
        if ($maxConn === null) {
            $result[] = 'Unlimited connections';
        } elseif ($maxConn > 0) {
            $result[] = "{$maxConn} connections/month";
        }

        $labels = [
            'public_listing'    => 'Public listing',
            'basic_profile'     => 'Basic profile',
            'view_profile_info' => 'Full profile access',
            'priority_listing'  => 'Priority listing',
            'analytics'         => 'Analytics',
            'send_leads'        => 'Send leads',
            'create_events'     => 'Create events',
            'featured_profile'  => 'Featured profile',
            'dedicated_support' => 'Dedicated support',
        ];

        foreach ($labels as $key => $display) {
            if (!empty($features[$key]) && $features[$key] === true) {
                $result[] = $display;
            }
        }

        return $result;
    }

    private function planPermissionsToArray(mixed $permissions): array
    {
        if (empty($permissions)) return [];

        // Already a flat string array — return as-is
        if (array_is_list($permissions) && isset($permissions[0]) && is_string($permissions[0])) {
            return $permissions;
        }

        // Load definitions ordered by sort_order
        $definitions = PermissionDefinition::orderBy('sort_order')->get()->keyBy('key');

        $result = [];
        foreach ($definitions as $key => $def) {
            $value = $permissions[$key] ?? null;

            if ($def->type === 'bool') {
                if ($value === true) {
                    $result[] = $def->label;
                }
            } elseif ($def->type === 'number') {
                if ($value === null && $def->null_label) {
                    $result[] = $def->null_label;
                } elseif (is_numeric($value) && $value > 0) {
                    $result[] = "{$value} {$def->label}";
                }
            }
        }

        return $result;
    }
}
