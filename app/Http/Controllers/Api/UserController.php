<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * UserController (API)
 * 
 * Handles user listing API endpoints.
 * Uses UserService for business logic (Hybrid architecture).
 */
class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get paginated users list.
     * 
     * GET /api/users?page=1&search=query
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentUserId = $request->user()->id;
            $page    = (int) $request->get('page', 1);
            $search       = $request->get('search', '');
            $searchFields = $request->input('search_fields', []);
            $perPage = 10;

            $filters = $request->only([
                'gender', 'age_min', 'age_max',
                'city_id', 'company', 'interests', 'open_to_network',
            ]);

            $users = $this->userService->getPaginatedUsers(
                $currentUserId,
                $page,
                $search,
                $perPage,
                $filters,
                $searchFields
            );

            // Return JSON response
            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'has_more_pages' => $users->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recommended users ordered by location + shared interests.
     *
     * GET /api/users/recommendations?page=1&search=query
     */
    public function recommendations(Request $request): JsonResponse
    {
        try {
            $currentUser = $request->user()->load(['profile', 'interests']);
            $page        = (int) $request->get('page', 1);
            $search      = $request->get('search', '') ?: null;

            // ?include_pending=1  → keep users with a pending request visible
            $excludeStatuses = $request->boolean('include_pending')
                ? ['accepted']
                : ['pending', 'accepted'];

            $users = $this->userService->getRecommendedUsers($currentUser, $page, $search, 10, $excludeStatuses);

            return response()->json([
                'success'       => true,
                'users'         => $users->items(),
                'current_page'  => $users->currentPage(),
                'last_page'     => $users->lastPage(),
                'per_page'      => $users->perPage(),
                'total'         => $users->total(),
                'has_more_pages'=> $users->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load recommendations', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to load recommendations'], 500);
        }
    }

    /**
     * Get user details by ID.
     *
     * GET /api/users/{id}
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $currentUserId = $request->user()->id;

            // Call service
            $user = $this->userService->getUserById($id, $currentUserId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user',
            ], 500);
        }
    }
}
