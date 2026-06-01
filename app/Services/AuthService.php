<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\LeadRating;
use App\Models\Sector;
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
        // Resolve mobile aliases before creating
        $data['phone_country_code'] = $data['phone_code'] ?? $data['phone_country_code'] ?? null;

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'first_name'         => $data['first_name'],
                'last_name'          => $data['last_name'],
                'email'              => $data['email'],
                'password'           => Hash::make($data['password']),
                'phone'              => $data['phone']              ?? null,
                'phone_country_code' => $data['phone_country_code'] ?? null,
                'city_id'     => $data['city_id']     ?? null,
                'nationality_id'     => $data['nationality_id']     ?? null,
                'gender'             => $data['gender']             ?? null,
                'birthday'           => $data['birthday']           ?? null,
                'role'               => 'user',
            ]);

            // Assign basic plan
            $this->assignBasicPlan($user);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User registration failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        // Send verification email outside the transaction — a mail failure
        // must not roll back the already-created account.
        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            Log::warning('Verification email could not be sent', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        Log::info('User registered successfully', ['user_id' => $user->id]);

        return $user;
    }

    /**
     * Update user profile information.
     * Persists to both the `users` table and the `profiles` table.
     *
     * @param User  $user
     * @param array $data  Validated data from ProfileRequest
     * @return User
     */
    public function updateProfile(User $user, array $data, ?\Illuminate\Http\UploadedFile $picture = null): User
    {
        // ── Normalize mobile aliases ──────────────────────────────────────
        $data['phone_country_code'] = $data['phone_code']    ?? $data['phone_country_code'] ?? null;
        $data['looking_for']        = $data['leads_wanted']  ?? $data['looking_for']        ?? null;
        $data['services_offered']   = $data['leads_offered'] ?? $data['services_offered']   ?? null;
        $data['job_title']          = $data['job_title']     ?? null;

        // ── users table ───────────────────────────────────────────────────
        $userFields = array_filter([
            'first_name'         => $data['first_name']         ?? null,
            'last_name'          => $data['last_name']          ?? null,
            'phone'              => $data['phone']              ?? null,
            'phone_country_code' => $data['phone_country_code'] ?? null,
            'gender'             => $data['gender']             ?? null,
            'birthday'           => $data['birthday']           ?? null,
            'city_id'     => $data['city_id']     ?? null,
            'nationality_id'     => $data['nationality_id']     ?? null,
            'company_id'         => $data['company_id']         ?? null,
            'newsletter'         => $data['newsletter']         ?? null,
            'notifications'      => $data['notifications']      ?? null,
        ], fn($v) => $v !== null);

        $userFields['onboarding_completed'] = true;
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
            'website'          => $data['website']          ?? null,
            'linkedin'         => $data['linkedin']         ?? null,
            'sector_ids'       => $data['sector_id']        ?? null,
        ], fn($v) => $v !== null);

        if (!empty($profileFields)) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        // ── avatar upload (profile_picture) ───────────────────────────────
        if ($picture) {
            if ($user->profile?->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile->avatar);
            }
            $path = $picture->store('avatars', 'public');
            $user->profile()->updateOrCreate(['user_id' => $user->id], ['avatar' => $path]);
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
        $user->load(['company.sector', 'subscription.plan', 'profile', 'nationality', 'city']);

        $sectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
            $user->profile?->sector_ids       ?? [],
        ));
        $sectorMap = $sectorIds
            ? Sector::whereIn('id', $sectorIds)->pluck('name', 'id')
            : collect();

        $rating = $this->ratingPayload($user);

        return [
            'user' => [
                'id'                 => $user->id,
                'first_name'         => $user->first_name,
                'last_name'          => $user->last_name,
                'full_name'          => $user->full_name,
                'email'              => $user->email,
                'phone'              => ['number' => $user->phone, 'code' => $user->phone_country_code],
                'gender'             => $user->gender,
                'birthday'           => $user->birthday?->format('Y-m-d'),
                'city'        => ['id' => $user->city_id, 'name' => $user->city?->name],
                'nationality'        => $user->nationality ? [
                    'id'      => $user->nationality->id,
                    'name'    => $user->nationality->name,
                    'country' => $user->nationality->country,
                    'code'    => $user->nationality->code,
                    'flag'    => $user->nationality->flag,
                ] : null,
                'newsletter'         => $user->newsletter,
                'notifications'      => $user->notifications,
                'role'               => $user->role,
                'balance'            => (int) ($user->points_balance ?? 0),
                'badge'              => $this->badgePayload($user->badge_level ?? 'bronze'),
                'rating'             => $rating,
                'email_verified_at'  => $user->email_verified_at,
                'created_at'         => $user->created_at,
            ],
            'profile' => $user->profile ? [
                'avatar'           => $user->profile->avatar_url,
                'bio'              => $user->profile->bio,
                'motto'            => $user->profile->motto,
                'job_title'        => $user->profile->job_title,
                'experience_level' => $user->profile->experience_level,
                'looking_for'      => collect($user->profile->looking_for ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
                'services_offered' => collect($user->profile->services_offered ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
                'sector_ids'       => collect($user->profile->sector_ids ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
                'website'          => $user->profile->website,
                'linkedin'         => $user->profile->linkedin,
            ] : null,
            'company' => $user->company ? [
                'id'      => $user->company->id,
                'name'    => $user->company->name,
                'siret'   => $user->company->siret,
                'website' => $user->company->website,
                'sector'  => $user->company->sector ? ['id' => $user->company->sector->id, 'name' => $user->company->sector->name] : null,
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
            'onboarding_completed' => (bool) ($user->onboarding_completed ?? false),
            'profile_completed'    => (bool) $user->hasCompletedProfile(),
        ];
    }

    private function ratingPayload(User $user): array
    {
        $stats = LeadRating::whereHas('lead', fn ($q) => $q->where('sender_id', $user->id))
            ->selectRaw('ROUND(AVG(average_note), 2) as average_rating, COUNT(*) as rating_count')
            ->first();

        return [
            'average' => $stats?->average_rating !== null ? (float) $stats->average_rating : null,
            'count'   => (int) ($stats?->rating_count ?? 0),
        ];
    }

    private function badgePayload(string $level): array
    {
        return match ($level) {
            'or' => [
                'level' => 'or',
                'label' => 'Or',
                'color' => '#B45309',
                'background' => '#FEF3C7',
            ],
            'argent' => [
                'level' => 'argent',
                'label' => 'Argent',
                'color' => '#475569',
                'background' => '#F1F5F9',
            ],
            default => [
                'level' => 'bronze',
                'label' => 'Bronze',
                'color' => '#92400E',
                'background' => '#FFEDD5',
            ],
        };
    }
}
