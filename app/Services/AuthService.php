<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\Plan;
use App\Models\SystemSetting;
use App\Models\Sector;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AuthService
 * 
 * Handles all authentication business logic.
 * Can be used by API controllers, Web controllers, Commands, Jobs, etc.
 */
class AuthService
{
    public function __construct(private ProfileVideoService $profileVideoService) {}

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
     * Find or create a user from LinkedIn OpenID profile data.
     * New LinkedIn users receive a token but remain onboarding-incomplete.
     */
    public function loginWithLinkedIn(array $linkedinUser): User
    {
        $email = strtolower((string) ($linkedinUser['email'] ?? ''));
        if ($email === '') {
            throw new \InvalidArgumentException('LinkedIn did not return an email address.');
        }

        $firstName = trim((string) ($linkedinUser['given_name'] ?? ''));
        $lastName = trim((string) ($linkedinUser['family_name'] ?? ''));

        if ($firstName === '' && $lastName === '') {
            $name = trim((string) ($linkedinUser['name'] ?? ''));
            $parts = preg_split('/\s+/', $name, 2) ?: [];
            $firstName = $parts[0] ?? 'LinkedIn';
            $lastName = $parts[1] ?? 'User';
        }

        $firstName = $firstName !== '' ? $firstName : 'LinkedIn';
        $lastName = $lastName !== '' ? $lastName : 'User';

        return DB::transaction(function () use ($email, $firstName, $lastName, $linkedinUser) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => Hash::make(Str::random(48)),
                    'role' => 'user',
                    'onboarding_completed' => false,
                ]);

                $user->forceFill(['email_verified_at' => now()])->save();

                $this->assignBasicPlan($user);
            } elseif (!$user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $profileFields = array_filter([
                'avatar' => $linkedinUser['picture'] ?? null,
            ], fn($value) => $value !== null && $value !== '');

            if (!empty($profileFields)) {
                $user->profile()->updateOrCreate(['user_id' => $user->id], $profileFields);
            }

            return $user->fresh();
        });
    }

    /**
     * Update user profile information.
     * Persists to both the `users` table and the `profiles` table.
     *
     * @param User  $user
     * @param array $data  Validated data from ProfileRequest
     * @return User
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $picture = null, ?UploadedFile $presentationVideo = null): User
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
            'looking_for'         => $data['looking_for']         ?? null,
            'services_offered'    => $data['services_offered']    ?? null,
            'open_to_network'     => $data['open_to_network']     ?? null,
            'website'             => $data['website']             ?? null,
            'linkedin'            => $data['linkedin']            ?? null,
            'sector_ids'          => $data['sector_id']           ?? null,
            'market_addressed_id' => $data['market_addressed_id'] ?? null,
            'market_target_id'    => $data['market_target_id']    ?? null,
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

        if ($presentationVideo) {
            $this->profileVideoService->store($user, $presentationVideo);
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

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $basicPlan->id,
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);

        if (($basicPlan->initial_points ?? 0) > 0) {
            $user->adjustPoints($basicPlan->initial_points, 'initial_balance');
        }

        return $subscription;
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
        DB::table('device_tokens')->where('user_id', $user->id)->delete();
    }

    /**
     * Get formatted user data for responses.
     *
     * @param User $user
     * @return array
     */
    public function getUserData(User $user): array
    {
        $user->load(['company.sector', 'subscription.plan', 'profile', 'nationality', 'city', 'consulRequests']);

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
                'badge'              => $this->badgePayload($user->badge_level ?? 'neutre'),
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
                'presentation_video' => $this->presentationVideoPayload($user->profile, true),
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
                'is_enterprise_owner' => $this->isEnterpriseOwnerSubscription($user->subscription),
            ] : null,
            'ambassador_status' => $user->ambassador_status ?? 'none',
            'consul_status'    => $this->deriveConsulStatus($user),
            'onboarding_completed' => (bool) ($user->onboarding_completed ?? false),
            'profile_completed'    => (bool) $user->hasCompletedProfile(),
        ];
    }

    private function deriveConsulStatus(User $user): ?string
    {
        $requests = $user->consulRequests;
        if ($requests->where('status', 'approved')->isNotEmpty()) return 'approved';
        if ($requests->where('status', 'pending')->isNotEmpty())  return 'pending';
        return $requests->sortByDesc('id')->first()?->status;
    }

    private function presentationVideoPayload(?\App\Models\Profile $profile, bool $includePrivateStatus = false): ?array
    {
        if (!$profile || !$profile->presentation_video) {
            return null;
        }

        $isApproved = $profile->presentation_video_status === ProfileVideoService::STATUS_APPROVED;

        return [
            'url' => $isApproved ? $profile->presentation_video_url : null,
            'status' => $includePrivateStatus ? $profile->presentation_video_status : ($isApproved ? $profile->presentation_video_status : null),
            'rejection_reason' => $includePrivateStatus ? $profile->presentation_video_rejection_reason : null,
            'uploaded_at' => $includePrivateStatus ? $profile->presentation_video_uploaded_at : null,
            'reviewed_at' => $includePrivateStatus ? $profile->presentation_video_reviewed_at : null,
        ];
    }

    private function isEnterpriseOwnerSubscription(?Subscription $subscription): bool
    {
        return $subscription !== null
            && $subscription->stripe_subscription_id !== null
            && ($subscription->plan?->max_users ?? 1) > 1;
    }

    private function ratingPayload(User $user): array
    {
        $stats = LeadRating::whereHas('lead', fn ($q) => $q->where('sender_id', $user->id))
            ->selectRaw('ROUND(AVG(average_note), 2) as average_rating, COUNT(*) as rating_count')
            ->first();

        $score = $this->computeRatingScore($user->id);

        return [
            'average' => $stats?->average_rating !== null ? (float) $stats->average_rating : null,
            'count'   => (int) ($stats?->rating_count ?? 0),
            'score'   => $score['score'],
            'stars'   => $score['stars'],
        ];
    }

    private function computeRatingScore(int $userId): array
    {
        $windowDays   = SystemSetting::get('scoring.window_days', 60);
        $givenMult    = SystemSetting::get('scoring.given_multiplier', 2);
        $receivedMult = SystemSetting::get('scoring.received_multiplier', -1);
        $mqlWeight    = SystemSetting::get('scoring.mql_weight', 1);
        $sqlWeight    = SystemSetting::get('scoring.sql_weight', 3);
        $spWeight     = SystemSetting::get('scoring.sp_weight', 5);

        $since = now()->subDays($windowDays);

        $given = Lead::where('sender_id', $userId)
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->where('updated_at', '>=', $since)
            ->get(['lead_type']);

        $receivedCount = Lead::where('receiver_id', $userId)
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->where('updated_at', '>=', $since)
            ->count();

        $givenCount = $given->count();
        $mql = $given->where('lead_type', Lead::TYPE_MQL)->count();
        $sql = $given->where('lead_type', Lead::TYPE_SQL)->count();
        $sp  = $given->where('lead_type', Lead::TYPE_SP)->count();

        $score = ($givenCount * $givenMult) + ($receivedCount * $receivedMult)
               + ($mql * $mqlWeight) + ($sql * $sqlWeight) + ($sp * $spWeight);
        $score = max(0, $score);
        $stars = min(5, (int) floor($score / 5));

        return ['score' => $score, 'stars' => $stars];
    }

    private function badgePayload(string $level): array
    {
        return match ($level) {
            'platinium' => [
                'level'      => 'platinium',
                'label'      => 'Platinium',
                'color'      => '#1D4ED8',
                'background' => '#EFF6FF',
            ],
            'or' => [
                'level'      => 'or',
                'label'      => 'Or',
                'color'      => '#B45309',
                'background' => '#FEF3C7',
            ],
            'argent' => [
                'level'      => 'argent',
                'label'      => 'Argent',
                'color'      => '#475569',
                'background' => '#F1F5F9',
            ],
            'bronze' => [
                'level'      => 'bronze',
                'label'      => 'Bronze',
                'color'      => '#92400E',
                'background' => '#FFEDD5',
            ],
            default => [
                'level'      => 'neutre',
                'label'      => 'Neutre',
                'color'      => '#9CA3AF',
                'background' => '#F9FAFB',
            ],
        };
    }
}
