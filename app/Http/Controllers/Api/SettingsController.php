<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Event;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Nationality;
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
            'event_categories'  => collect(Event::$categoryLabels)
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
                                   ->get(['id', 'name', 'label', 'description', 'price', 'billing_period', 'max_leads', 'max_groups', 'max_users', 'features']),

            'cities'        => City::with('country:id,name,code,flag')
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
}
