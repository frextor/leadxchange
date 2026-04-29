<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * AuthService
 * 
 * Handles all authentication business logic.
 * Can be used by API controllers, Web controllers, Commands, Jobs, etc.
 */
class AuthService
{
    /**
     * Register a new user with basic plan.
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function register(array $data): User
    {
        DB::beginTransaction();
        
        try {
            // Create user
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user',
            ]);

            // Fire the registered event for email verification
            event(new Registered($user));

            // Assign basic plan
            $this->assignBasicPlan($user);

            DB::commit();

            Log::info('User registered successfully', ['user_id' => $user->id]);

            return $user;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User registration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update user profile information.
     *
     * @param User $user
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function updateProfile(User $user, array $data): User
    {
        // Check if user has already completed profile
        if ($user->hasCompletedProfile()) {
            throw new \Exception('Profile already completed');
        }

        $user->update([
            'gender' => $data['gender'],
            'city_birth' => $data['city_birth'],
            'city_living' => $data['city_living'],
            'birthday' => $data['birthday'],
        ]);

        Log::info('User profile updated', ['user_id' => $user->id]);

        return $user->fresh();
    }

    /**
     * Assign basic plan to user.
     *
     * @param User $user
     * @return Subscription|null
     */
    private function assignBasicPlan(User $user): ?Subscription
    {
        $basicPlan = Plan::where('name', 'basic')->first();
        
        if (!$basicPlan) {
            Log::warning('Basic plan not found');
            return null;
        }

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $basicPlan->id,
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    /**
     * Create authentication token for user.
     *
     * @param User $user
     * @param string $tokenName
     * @return string
     */
    public function createToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke all tokens for user.
     *
     * @param User $user
     * @return void
     */
    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Revoke current token.
     *
     * @param User $user
     * @return void
     */
    public function revokeCurrentToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Get formatted user data for responses.
     *
     * @param User $user
     * @return array
     */
    public function getUserData(User $user): array
    {
        // Load relationships
        $user->load(['company', 'subscription.plan']);

        return [
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
    }
}
