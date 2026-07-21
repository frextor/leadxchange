<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\ProfileRequest;
use App\Services\AuthService;
use App\Services\CompanyService;
use App\Services\EnterpriseInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Throwable;

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
    protected EnterpriseInvitationService $enterpriseInvitationService;

    /**
     * Inject services via constructor.
     */
    public function __construct(
        AuthService $authService,
        CompanyService $companyService,
        EnterpriseInvitationService $enterpriseInvitationService,
    )
    {
        $this->authService = $authService;
        $this->companyService = $companyService;
        $this->enterpriseInvitationService = $enterpriseInvitationService;
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
            $validated = $request->validated();
            $invitationToken = $validated['invitation_token'] ?? null;

            if ($invitationToken) {
                $this->enterpriseInvitationService->assertTokenCanBeAcceptedByEmail(
                    $invitationToken,
                    $validated['email'],
                );
            }

            // Service handles ALL business logic
            $user = $this->authService->register($validated);

            if ($invitationToken) {
                $this->enterpriseInvitationService->acceptForUser($invitationToken, $user);
                $user = $user->fresh();
            }

            // Mark referral as registered if token provided\n            $referralToken = $validated['referral_token'] ?? null;\n            if ($referralToken) {\n                \\App\\Models\\Referral::where('token', $referralToken)\n                    ->where('status', 'pending')\n                    ->update(['status' => 'registered']);\n            }\n\n            // Create token
            $token = $this->authService->createToken($user);

            // Return JSON response
            return response()->json([
                'message' => 'Registration successful. Please verify your email and complete your profile.',
                'data' => $this->authService->getUserData($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
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
     * Login or register a user with LinkedIn OpenID Connect.
     */
    public function linkedin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'           => ['required', 'string'],
            'referral_token' => ['nullable', 'string', 'max:255'],
        ]);

        $clientId = config('services.linkedin.client_id');
        $clientSecret = config('services.linkedin.client_secret');
        $redirectUri = config('services.linkedin.redirect_uri');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            return response()->json([
                'message' => 'LinkedIn login is not configured.',
            ], 500);
        }

        try {
            $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'authorization_code',
                'code' => $validated['code'],
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('LinkedIn token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->json() ?: $tokenResponse->body(),
                    'redirect_uri' => $redirectUri,
                ]);

                if ($tokenResponse->status() === 429) {
                    return response()->json([
                        'message' => 'Too many LinkedIn login attempts. Please try again in a few minutes.',
                    ], 429);
                }

                return response()->json([
                    'message' => 'LinkedIn login failed. Please try again.',
                ], 422);
            }

            $accessToken = $tokenResponse->json('access_token');
            if (!$accessToken) {
                return response()->json([
                    'message' => 'LinkedIn login failed. Please try again.',
                ], 422);
            }

            $userInfoResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->get('https://api.linkedin.com/v2/userinfo');

            if (!$userInfoResponse->successful()) {
                Log::error('LinkedIn userinfo request failed', [
                    'status' => $userInfoResponse->status(),
                    'body' => $userInfoResponse->json() ?: $userInfoResponse->body(),
                ]);

                return response()->json([
                    'message' => 'LinkedIn profile could not be loaded.',
                ], 422);
            }

            $user = $this->authService->loginWithLinkedIn($userInfoResponse->json(), $validated['referral_token'] ?? null);
            $this->authService->revokeAllTokens($user);
            $token = $this->authService->createToken($user);

            return response()->json([
                'message' => 'LinkedIn login successful',
                'data' => $this->authService->getUserData($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (\Throwable $e) {
            Log::error('LinkedIn login failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'LinkedIn login failed. Please try again.',
            ], 500);
        }
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
            $user = $this->authService->updateProfile(
                $request->user(),
                $request->validated(),
                $request->file('profile_picture'),
                $request->file('presentation_video')
            );

            return response()->json([
                'message' => 'Profile updated successfully.',
                'data'    => $this->authService->getUserData($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            Log::error('Password reset email failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Password reset email could not be sent right now. Please try again later.',
            ], 503);
        }

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Reset link sent to your email.']);
        }

        return response()->json(['message' => __($status)], 422);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.']);
        }

        return response()->json(['message' => __($status)], 422);
    }

    /**
     * POST /api/company
     *
     * @deprecated Use POST /api/companies instead.
     */
    public function createCompany(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:255'],
                'siret'     => ['required', 'string', 'size:14', 'unique:companies,siret', 'regex:/^[0-9]{14}$/'],
                'sector_id' => ['nullable', 'integer', 'exists:sectors,id'],
                'website'   => ['nullable', 'url', 'max:255'],
            ]);

            $this->companyService->createCompany($request->user(), $validated);

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'data'    => $this->authService->getUserData($request->user()->fresh()),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
