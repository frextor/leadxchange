<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use Illuminate\Http\JsonResponse;

class SectorController extends Controller
{
    /**
     * GET /api/sectors
     * Returns all sectors ordered by name.
     */
    public function index(): JsonResponse
    {
        return response()->json(Sector::orderBy('name')->get(['id', 'name']));
    }
}
