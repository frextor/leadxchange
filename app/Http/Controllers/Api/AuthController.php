<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\ProfileRequest;
use App\Services\AuthService;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AuthController (REFACTORED)
 * 
 * Thin controller - only handles HTTP requests/responses.
 * All business logic is in AuthService.
 */
class AuthController extends Controller
{
    protected AuthService $authService;
    protected CompanyService $companyService;

    /**
     * Inject services via constructor.
     */
    public function __construct(AuthService $authService, CompanyService $companyService)
    {
        $this->authService = $authService;
        $this->companyService = $companyService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Service handles ALL business logic
            $user = $this->authService->register($request->validated());

            // Create token
            $token = $this->authService->createToken($user);

            // Return JSON response
            return response()->json([
                'message' => 'Registration successful. Please verify your email and complete your profile.',
                'data' => $this->authService->getUserData($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        // Revoke all previous tokens
        $this->authService->revokeAllTokens($user);

        // Create new token
        $token = $this->authService->createToken($user);

        return response()->json([
            'message' => 'Login successful',
            'data' => $this->authService->getUserData($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Service handles token revocation
        $this->authService->revokeCurrentToken($request->user());

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->authService->getUserData($request->user()),
        ]);
    }

    /**
     * Update user profile.
     *
     * @param ProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(ProfileRequest $request): JsonResponse
    {
        try {
            // Service handles ALL business logic
            $user = $this->authService->updateProfile(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Profile updated successfully. Please create your company to complete onboarding.',
                'data' => $this->authService->getUserData($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create company (legacy method - kept for backward compatibility).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCompany(Request $request): JsonResponse
    {
        // Validate
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'siret' => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
            'sector' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        try {
            // Service handles ALL business logic
            $company = $this->companyService->createCompany(
                $request->user(),
                $validated
            );

            return response()->json([
                'message' => 'Company created successfully. Onboarding completed!',
                'data' => $this->authService->getUserData($request->user()->fresh()),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
