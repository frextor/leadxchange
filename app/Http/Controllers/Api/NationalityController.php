<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use Illuminate\Http\JsonResponse;

class NationalityController extends Controller
{
    /**
     * GET /api/nationalities
     * Returns all nationalities ordered by country name.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Nationality::orderBy('country')->get(['id', 'name', 'country', 'code', 'flag'])
        );
    }
}
