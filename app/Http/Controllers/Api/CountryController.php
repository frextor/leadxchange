<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * GET /api/countries
     * Returns all countries ordered by name.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Country::orderBy('name')->get(['id', 'name', 'code', 'flag'])
        );
    }
}
