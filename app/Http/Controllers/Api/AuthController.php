<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user and assign basic plan.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
            ]);

            // Fire the registered event for email verification
            event(new Registered($user));

            // Assign basic plan
            $basicPlan = Plan::where('name', 'basic')->first();
            
            if ($basicPlan) {
                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $basicPlan->id,
                    'status' => 'active',
                    'trial_ends_at' => now()->addDays(14), // 14 days trial
                ]);
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'Registration successful. Please verify your email and complete your profile.',
                'data' => $this->getUserData($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user and create token.
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

        $user = User::where('email', $request->email)->firstOrFail();

        // Revoke all previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => $this->getUserData($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (revoke token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

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
            'data' => $this->getUserData($request->user()),
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
        $user = $request->user();

        // Check if user has already completed profile
        if ($user->hasCompletedProfile()) {
            return response()->json([
                'message' => 'Profile already completed',
            ], 400);
        }

        $user->update([
            'gender' => $request->gender,
            'city_birth' => $request->city_birth,
            'city_living' => $request->city_living,
            'birthday' => $request->birthday,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully. Please create your company to complete onboarding.',
            'data' => $this->getUserData($user->fresh()),
        ]);
    }

    /**
     * Create company and complete onboarding.
     *
     * @param CompanyRequest $request
     * @return JsonResponse
     */
    public function createCompany(CompanyRequest $request): JsonResponse
    {
        $user = $request->user();

        // Check if profile is completed
        if (!$user->hasCompletedProfile()) {
            return response()->json([
                'message' => 'Please complete your profile before creating a company',
            ], 400);
        }

        // Check if user already has a company
        if ($user->company_id) {
            return response()->json([
                'message' => 'User already belongs to a company',
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Create company
            $company = Company::create([
                'name' => $request->name,
                'siret' => $request->siret,
                'sector' => $request->sector,
                'website' => $request->website,
            ]);

            // Update user with company and mark onboarding as completed
            $user->update([
                'company_id' => $company->id,
                'onboarding_completed' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Company created successfully. Onboarding completed!',
                'data' => $this->getUserData($user->fresh()),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Company creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get formatted user data for API responses.
     *
     * @param User $user
     * @return array
     */
    private function getUserData(User $user): array
    {
        // Load relationships
        $user->load(['company', 'subscription.plan']);

        $data = [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'gender' => $user->gender,
                'city_birth' => $user->city_birth,
                'city_living' => $user->city_living,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
            'company' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'siret' => $user->company->siret,
                'sector' => $user->company->sector,
                'website' => $user->company->website,
            ] : null,
            'subscription' => $user->subscription ? [
                'id' => $user->subscription->id,
                'status' => $user->subscription->status,
                'trial_ends_at' => $user->subscription->trial_ends_at,
                'ends_at' => $user->subscription->ends_at,
                'on_trial' => $user->subscription->onTrial(),
            ] : null,
            'plan' => $user->subscription?->plan ? [
                'id' => $user->subscription->plan->id,
                'name' => $user->subscription->plan->name,
                'price' => $user->subscription->plan->price,
                'features' => $user->subscription->plan->features,
            ] : null,
            'onboarding_completed' => $user->onboarding_completed,
            'profile_completed' => $user->hasCompletedProfile(),
        ];

        return $data;
    }
}
