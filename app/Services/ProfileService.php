<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\Profile;
use App\Models\SystemSetting;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(private ProfileVideoService $profileVideoService) {}

    public function getProfile(User $user): array
    {
        $user->loadMissing(['profile', 'interests', 'company', 'city']);

        $sectorIds = array_unique(array_merge(
            $user->profile?->looking_for      ?? [],
            $user->profile?->services_offered ?? [],
        ));
        $sectorMap = $sectorIds
            ? Sector::whereIn('id', $sectorIds)->pluck('name', 'id')
            : collect();

        $profile = $user->profile;
        $rating = $this->ratingPayload($user);

        $user->loadMissing('subscription.plan');

        return [
            'id'              => $user->id,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'email'           => $user->email,
            'gender'          => $user->gender,
            'city'     => ['id' => $user->city_id, 'name' => $user->city?->name],
            'birthday'        => $user->birthday?->format('Y-m-d'),
            'member_since' => $user->created_at?->format('F Y'),
            'profile'      => $profile ? array_merge($profile->toArray(), [
                'looking_for'      => collect($profile->looking_for ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
                'services_offered' => collect($profile->services_offered ?? [])->map(fn($id) => ['id' => $id, 'name' => $sectorMap[$id] ?? null])->values(),
                'presentation_video' => $this->presentationVideoPayload($profile),
            ]) : null,
            'interests'    => $user->interests,
            'company'      => $user->company ? [
                'id'      => $user->company->id,
                'name'    => $user->company->name,
                'siret'   => $user->company->siret,
                'website' => $user->company->website,
                'sector'  => $user->company->sector ? ['id' => $user->company->sector->id, 'name' => $user->company->sector->name] : null,
            ] : null,
            'balance'           => (int) ($user->points_balance ?? 0),
            'badge'             => $this->badgePayload($user->badge_level ?? 'neutre'),
            'rating'            => $rating,
            'completion'        => $this->getCompletionPercentage($user),
            'consul_status'     => $user->consul_status,
            'ambassador_status' => $user->ambassador_status,
            'subscription'      => $user->subscription ? [
                'status' => $user->subscription->status,
                'plan'   => $user->subscription->plan ? [
                    'id'    => $user->subscription->plan->id,
                    'name'  => $user->subscription->plan->name,
                    'label' => $user->subscription->plan->label,
                    'price' => $user->subscription->plan->price,
                ] : null,
            ] : null,
        ];
    }

    public function updateBasicInfo(User $user, array $data): void
    {
        $userFields = array_filter(
            array_intersect_key($data, array_flip(['first_name', 'last_name', 'gender', 'city_id', 'birthday', 'phone'])),
            fn($v) => $v !== null
        );

        if (!empty($userFields)) {
            $user->update($userFields);
        }
    }

    public function updateProfessional(User $user, array $data): void
    {
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $fields  = array_intersect_key($data, array_flip([
            'job_title', 'sector', 'experience_level', 'looking_for', 'services_offered',
        ]));
        $profile->fill(array_filter($fields, fn($v) => $v !== null));
        if (array_key_exists('looking_for', $data))      $profile->looking_for      = $data['looking_for']      ?? [];
        if (array_key_exists('services_offered', $data)) $profile->services_offered = $data['services_offered'] ?? [];
        if (array_key_exists('market_addressed_id', $data)) $profile->market_addressed_id = $data['market_addressed_id'] ?: null;
        if (array_key_exists('market_target_id', $data))    $profile->market_target_id    = $data['market_target_id']    ?: null;
        $profile->save();
    }

    public function updateBio(User $user, array $data): void
    {
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill([
            'bio'             => $data['bio']             ?? $profile->bio,
            'motto'           => $data['motto']           ?? $profile->motto,
            'open_to_network' => $data['open_to_network'] ?? $profile->open_to_network,
        ]);
        $profile->save();
    }

    public function updateAvatar(User $user, UploadedFile $file): string
    {
        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);

        if ($profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $path = $file->store('avatars', 'public');
        $profile->update(['avatar' => $path]);

        return Storage::disk('public')->url($path);
    }

    public function updatePresentationVideo(User $user, UploadedFile $file): array
    {
        $this->profileVideoService->storePendingUpload($user, $file);

        return $this->presentationVideoPayload($user->fresh('profile')->profile);
    }

    public function syncInterests(User $user, array $interestIds): void
    {
        $user->interests()->sync($interestIds);
    }

    public function getCompletionPercentage(User $user): int
    {
        $user->loadMissing(['profile', 'company']);
        $p = $user->profile;

        $checks = [
            fn() => !empty($user->first_name) && !empty($user->last_name),
            fn() => !empty($p?->job_title),
            fn() => !is_null($user->company_id),
            fn() => !is_null($user->city_id),
            fn() => !empty($p?->sector_ids),
            fn() => !empty($p?->bio),
            fn() => !empty($user->phone),
            fn() => !empty($p?->avatar),
            fn() => !empty($p?->services_offered),
            fn() => !empty($p?->looking_for),
        ];

        $done = collect($checks)->filter(fn($c) => $c())->count();
        return (int) round(($done / count($checks)) * 100);
    }

    public function getMissingFields(User $user): array
    {
        $user->loadMissing(['profile', 'company']);
        $p = $user->profile;
        $missing = [];

        if (empty($user->first_name) || empty($user->last_name)) $missing[] = ['key' => 'name',        'label' => 'Nom',              'icon' => 'fa-user'];
        if (empty($p?->job_title))                              $missing[] = ['key' => 'job_title',   'label' => 'Poste',            'icon' => 'fa-briefcase'];
        if (is_null($user->company_id))                         $missing[] = ['key' => 'company',     'label' => 'Entreprise',       'icon' => 'fa-building'];
        if (is_null($user->city_id))                            $missing[] = ['key' => 'location',    'label' => 'Localisation',     'icon' => 'fa-map-marker-alt'];
        if (empty($p?->sector_ids))                             $missing[] = ['key' => 'sector',      'label' => 'Secteur',          'icon' => 'fa-industry'];
        if (empty($p?->bio))                                    $missing[] = ['key' => 'bio',         'label' => 'Bio',              'icon' => 'fa-pen'];
        if (empty($user->phone))                                $missing[] = ['key' => 'phone',       'label' => 'Téléphone',        'icon' => 'fa-phone'];
        if (empty($p?->avatar))                                 $missing[] = ['key' => 'avatar',      'label' => 'Photo de profil',  'icon' => 'fa-camera'];
        if (empty($p?->services_offered))                       $missing[] = ['key' => 'services',    'label' => 'Services offerts', 'icon' => 'fa-handshake'];
        if (empty($p?->looking_for))                            $missing[] = ['key' => 'looking_for', 'label' => 'Recherche',        'icon' => 'fa-search'];

        return $missing;
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

        $since = now()->subHours(1); // TEST (rollback: subDays($windowDays))

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

    private function presentationVideoPayload(?Profile $profile): ?array
    {
        if (!$profile || !$profile->presentation_video) {
            return null;
        }

        $isApproved = $profile->presentation_video_status === ProfileVideoService::STATUS_APPROVED;

        return [
            'url' => $isApproved ? $profile->presentation_video_url : null,
            'status' => $profile->presentation_video_status,
            'rejection_reason' => $profile->presentation_video_rejection_reason,
            'uploaded_at' => $profile->presentation_video_uploaded_at,
            'reviewed_at' => $profile->presentation_video_reviewed_at,
        ];
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
