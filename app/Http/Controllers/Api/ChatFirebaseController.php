<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatFirebaseController extends Controller
{
    public function __construct(private readonly FirebaseService $firebaseService) {}

    /**
     * Issue a Firebase Auth custom token for the authenticated user.
     * The mobile app exchanges this token via signInWithCustomToken() to authenticate
     * with Firebase Realtime Database and read chat data.
     */
    public function token(Request $request): JsonResponse
    {
        $token = $this->firebaseService->createChatCustomToken($request->user()->id);

        return response()->json(['firebase_token' => $token]);
    }
}
