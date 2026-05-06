<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    /**
     * GET /api/cities?q=casa&country_id=1
     *
     * Requires at least 2 characters.
     * Optional: filter by country_id.
     * Returns max 20 results, Morocco first.
     */
    public function index(Request $request): JsonResponse
    {
        $q         = trim($request->get('q', ''));
        $countryId = $request->get('country_id');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $cities = City::with('country:id,name,code,flag')
            ->where('name', 'LIKE', "{$q}%")
            ->when($countryId, fn($query) => $query->where('country_id', $countryId))
            ->orderByRaw("CASE WHEN country_id = (SELECT id FROM countries WHERE code = 'MA') THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'country_id']);

        return response()->json($cities->map(fn($city) => [
            'id'           => $city->id,
            'name'         => $city->name,
            'country_id'   => $city->country_id,
            'country_name' => $city->country?->name,
            'country_code' => $city->country?->code,
            'flag'         => $city->country?->flag,
        ]));
    }
}
