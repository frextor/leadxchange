<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    /**
     * GET /api/languages
     * Returns all languages ordered by name.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Language::orderBy('name')->get(['id', 'name', 'native_name', 'code'])
        );
    }
}
