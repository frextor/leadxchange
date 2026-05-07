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
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'phone'      => $data['phone'],
                'role'       => 'user',
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
     * Persists to both the `users` table and the `profiles` table.
     *
     * @param User  $user
     * @param array $data  Validated data from ProfileRequest
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        // ── users table ───────────────────────────────────────────────────
        $userFields = array_filter([
            'first_name'     => $data['first_name']     ?? null,
            'last_name'      => $data['last_name']      ?? null,
            'phone'          => $data['phone']          ?? null,
            'gender'         => $data['gender'],
            'birthday'       => $data['birthday'],
            'city_birth'     => $data['city_birth'],
            'city_living'    => $data['city_living'],
            'nationality_id' => $data['nationality_id'] ?? null,
        ], fn($v) => $v !== null);

        $user->update($userFields);

        // ── profiles table (upsert) ────────────────────────────────────────
        $profileFields = array_filter([
            'bio'              => $data['bio']              ?? null,
            'motto'            => $data['motto']            ?? null,
            'job_title'        => $data['job_title']        ?? null,
            'experience_level' => $data['experience_level'] ?? null,
            'looking_for'      => $data['looking_for']      ?? null,
            'services_offered' => $data['services_offered'] ?? null,
            'open_to_network'  => $data['open_to_network']  ?? null,
        ], fn($v) => $v !== null);

        if (!empty($profileFields)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

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
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    }

    /**
     * Get formatted user data for responses.
     *
     * @param User $user
     * @return array
     */
    public function getUserData(User $user): array
    {
        $user->load(['company.sector', 'subscription.plan', 'profile', 'nationality']);

        return [
            'user' => [
                'id'                 => $user->id,
                'first_name'         => $user->first_name,
                'last_name'          => $user->last_name,
                'full_name'          => $user->full_name,
                'email'              => $user->email,
                'phone'              => $user->phone,
                'gender'             => $user->gender,
                'birthday'           => $user->birthday?->format('Y-m-d'),
                'city_birth'         => $user->city_birth,
                'city_living'        => $user->city_living,
                'nationality_id'     => $user->nationality_id,
                'nationality'        => $user->nationality ? [
                    'id'      => $user->nationality->id,
                    'name'    => $user->nationality->name,
                    'country' => $user->nationality->country,
                    'code'    => $user->nationality->code,
                    'flag'    => $user->nationality->flag,
                ] : null,
                'position'           => $user->position,
                'role'               => $user->role,
                'email_verified_at'  => $user->email_verified_at,
                'created_at'         => $user->created_at,
            ],
            'profile' => $user->profile ? [
                'avatar'           => $user->profile->avatar_url,
                'bio'              => $user->profile->bio,
                'motto'            => $user->profile->motto,
                'job_title'        => $user->profile->job_title,
                'experience_level' => $user->profile->experience_level,
                'looking_for'      => $user->profile->looking_for,
                'services_offered' => $user->profile->services_offered,
                'open_to_network'  => $user->profile->open_to_network,
            ] : null,
            'company' => $user->company ? [
                'id'        => $user->company->id,
                'name'      => $user->company->name,
                'siret'     => $user->company->siret,
                'sector_id' => $user->company->sector_id,
                'sector'    => $user->company->sector?->name,
                'website'   => $user->company->website,
            ] : null,
            'subscription' => $user->subscription ? [
                'id'            => $user->subscription->id,
                'status'        => $user->subscription->status,
                'trial_ends_at' => $user->subscription->trial_ends_at,
                'ends_at'       => $user->subscription->ends_at,
                'on_trial'      => $user->subscription->onTrial(),
            ] : null,
            'plan' => $user->subscription?->plan ? [
                'id'       => $user->subscription->plan->id,
                'name'     => $user->subscription->plan->name,
                'price'    => $user->subscription->plan->price,
                'features' => $user->subscription->plan->features,
            ] : null,
            'onboarding_completed' => $user->onboarding_completed,
            'profile_completed'    => $user->hasCompletedProfile(),
        ];
    }
}
