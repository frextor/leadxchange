<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => 'Feedback submitted.'], 201);
    }
}
