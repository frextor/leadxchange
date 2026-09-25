<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Connection;
use App\Models\LeadRating;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class UserService
{
    /** Cache per-request so list endpoints don't repeat the same query N times. */
    private array $viewerPermissionCache = [];

    private function viewerCanViewMemberName(int $currentUserId): bool
    {
        if (!isset($this->viewerPermissionCache[$currentUserId])) {
            $viewer = User::find($currentUserId);
            $this->viewerPermissionCache[$currentUserId] =
                (bool) ($viewer?->effectivePlan()?->permissions['can_view_member_name'] ?? false);
        }
        return $this->viewerPermissionCache[$currentUserId];
    }


    /**
     * Get paginated users with connection status and optional filters.
     *
     * @param int         $currentUserId
     * @param int         $page
     * @param string|null $search  Basic name/email/city search
     * @param int         $perPage
     * @param array       $filters Advanced filters: gender, age_min, age_max,
     *                             city_id, company, interests (array of IDs), open_to_network
     */
    public function getPaginatedUsers(
        int $currentUserId,
        int $page = 1,
        ?string $search = null,
        int $perPage = 10,
        array $filters = [],
        array $searchFields = []
    ): LengthAwarePaginator {

        // Load current user's sector IDs once for shared-interest computation
        $mySectorIds = User::with('profile:user_id,sector_ids')->find($currentUserId)?->profile?->sector_ids ?? [];

        $query = User::query()
            ->regular()
            ->where('id', '!=', $currentUserId)
            ->with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status,website,linkedin,market_addressed_id,market_target_id', 'city:id,name', 'consulRequests', 'subscription.plan:id,name,label'])
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'phone_country_code', 'city_id', 'birthday', 'gender', 'company_id', 'points_balance', 'badge_level', 'ambassador_status', 'consul_status']);

        // Search — scoped to requested fields (or all fields if none specified)
        if ($search) {
            $like   = "%{$search}%";
            $fields = $searchFields;
            $query->where(function ($q) use ($like, $fields) {
                $all = empty($fields);
                if ($all || in_array('name', $fields)) {
                    $q->orWhere('first_name', 'LIKE', $like)
                      ->orWhere('last_name',  'LIKE', $like);
                }
                if ($all || in_array('company', $fields)) {
                    $q->orWhereHas('company', fn ($c) => $c->where('name', 'LIKE', $like));
                }
                if ($all || in_array('job_title', $fields)) {
                    $q->orWhereHas('profile', fn ($p) => $p->where('job_title', 'LIKE', $like));
                }
                if ($all || in_array('city', $fields)) {
                    $q->orWhereHas('city', fn ($c) => $c->where('name', 'LIKE', $like));
                }
                if ($all || in_array('nationality', $fields)) {
                    $q->orWhereHas('nationality', fn ($n) => $n->where('name', 'LIKE', $like));
                }
                if ($all || in_array('services_offered', $fields)) {
                    $q->orWhereHas('profile', fn ($p) => $p->whereExists(
                        fn ($sub) => $sub->selectRaw('1')
                            ->from('sectors')
                            ->whereRaw('JSON_CONTAINS(profiles.services_offered, CAST(sectors.id AS CHAR))')
                            ->where('sectors.name', 'LIKE', $like)
                    ));
                }
                if ($all || in_array('looking_for', $fields)) {
                    $q->orWhereHas('profile', fn ($p) => $p->whereExists(
                        fn ($sub) => $sub->selectRaw('1')
                            ->from('sectors')
                            ->whereRaw('JSON_CONTAINS(profiles.looking_for, CAST(sectors.id AS CHAR))')
                            ->where('sectors.name', 'LIKE', $like)
                    ));
                }
            });
        }

        // Gender
        if (!empty($filters['gender']) && $filters['gender'] !== 'all') {
            $query->where('gender', $filters['gender']);
        }

        // Age range (derived from birthday)
        if (!empty($filters['age_min'])) {
            $query->whereNotNull('birthday')
                  ->whereDate('birthday', '<=', now()->subYears((int) $filters['age_min'])->toDateString());
        }
        if (!empty($filters['age_max'])) {
            $query->whereNotNull('birthday')
                  ->whereDate('birthday', '>=', now()->subYears((int) $filters['age_max'] + 1)->toDateString());
        }

        // City of living (by ID)
        if (!empty($filters['city_id'])) {
            $query->where('city_id', (int) $filters['city_id']);
        }

        // Company name
        if (!empty($filters['company'])) {
            $query->whereHas('company', function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['company'] . '%');
            });
        }

        // Interests (array of interest IDs)
        if (!empty($filters['interests'])) {
            $ids = array_filter((array) $filters['interests']);
            if (!empty($ids)) {
                $query->whereHas('interests', fn ($q) => $q->whereIn('interests.id', $ids));
            }
        }

        // Open to network
        if (!empty($filters['open_to_network'])) {
            $query->whereHas('profile', fn ($q) => $q->where('open_to_network', true));
        }

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        $users->getCollection()->transform(
            fn ($user) => $this->enrichUserWithConnectionStatus($user, $currentUserId, $mySectorIds)
        );

        return $users;
    }

    /**
     * Get recommended users ordered by location proximity then shared interests.
     *
     * Scoring (per user):
     *   +30 — same city as current user
     *   +5  — per shared interest (user_interests pivot)
     */
    /**
     * @param array $excludeConnectionStatuses  Statuses to exclude from results.
     *                                           Default ['pending','accepted'] = exclude everyone already connected or pending.
     *                                           Pass ['accepted'] to keep pending users visible in recommendations.
     */
    public function getRecommendedUsers(
        User $currentUser,
        int $page = 1,
        ?string $search = null,
        int $perPage = 10,
        array $excludeConnectionStatuses = ['pending', 'accepted']
    ): LengthAwarePaginator {
        $myCityId    = $currentUser->city_id;
        $mySectorIds = $currentUser->profile?->sector_ids ?? [];
        $myInterestIds = $mySectorIds; // kept for scoring SQL compatibility (unused now)

        if (empty($myInterestIds)) {
            $interestSql      = '0';
            $interestBindings = [];
        } else {
            $phs              = implode(',', array_fill(0, count($myInterestIds), '?'));
            $interestSql      = "(SELECT COUNT(*) FROM user_interests ui WHERE ui.user_id = users.id AND ui.interest_id IN ({$phs})) * 5";
            $interestBindings = $myInterestIds;
        }

        // Closure applied to both count and data queries
        $applyWhere = function ($q) use ($currentUser, $search, $excludeConnectionStatuses, $myCityId) {
            $q->whereNotIn('users.role', ['admin', 'super_admin'])
              ->where('users.id', '!=', $currentUser->id)
              ->whereNotExists(function ($sub) use ($currentUser, $excludeConnectionStatuses) {
                  $sub->from('connections')
                      ->whereIn('status', $excludeConnectionStatuses)
                      ->where(function ($c) use ($currentUser) {
                          $c->where(function ($c2) use ($currentUser) {
                              $c2->where('sender_id', $currentUser->id)
                                 ->whereColumn('receiver_id', 'users.id');
                          })->orWhere(function ($c2) use ($currentUser) {
                              $c2->where('receiver_id', $currentUser->id)
                                 ->whereColumn('sender_id', 'users.id');
                          });
                      });
              });
            if ($myCityId !== null) {
                $q->where('users.city_id', $myCityId);
            }
            if ($search) {
                $like = "%{$search}%";
                $q->where(function ($q2) use ($like) {
                    $q2->where('users.first_name',   'LIKE', $like)
                       ->orWhere('users.last_name',   'LIKE', $like)
                       ->orWhere('profiles.job_title','LIKE', $like)
                       ->orWhereExists(function ($sub) use ($like) {
                           $sub->from('cities')
                               ->whereColumn('cities.id', 'users.city_id')
                               ->where('cities.name', 'LIKE', $like);
                       });
                });
            }
        };

        // Count (no selectRaw needed)
        $total = User::leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
            ->tap($applyWhere)
            ->count('users.id');

        // Data — scored and ordered
        $scoreBindings = array_merge([$myCityId], $interestBindings);

        $users = User::select('users.*')
            ->selectRaw("
                (CASE WHEN users.city_id = ? AND users.city_id IS NOT NULL THEN 30 ELSE 0 END) +
                {$interestSql} as rec_score
            ", $scoreBindings)
            ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
            ->with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status', 'city:id,name', 'consulRequests', 'subscription.plan:id,name,label'])
            ->tap($applyWhere)
            ->orderBy('rec_score', 'desc')
            ->orderBy('users.created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $enriched = $users->map(fn ($u) => array_merge(
            $this->enrichUserWithConnectionStatus($u, $currentUser->id, $mySectorIds),
            [
                'same_city' => $myCityId !== null && $u->city_id === $myCityId,
                'rec_score' => (int) ($u->rec_score ?? 0),
            ]
        ));

        return new LengthAwarePaginator($enriched, $total, $perPage, $page, [
            'path' => \Illuminate\Support\Facades\Request::url(),
        ]);
    }

    /**
     * Enrich a user with connection status, shared sectors, and profile fields.
     */
    public function enrichUserWithConnectionStatus(
        User $user,
        int $currentUserId,
        array $mySectorIds = []
    ): array {
        $connection = Connection::where(function ($q) use ($user, $currentUserId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $currentUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $currentUserId);
        })->first();

        $theirSectorIds  = $user->profile?->sector_ids ?? [];

        $isSelf          = $user->id === $currentUserId;
        $canViewName     = $isSelf || $this->viewerCanViewMemberName($currentUserId);

        return [
            'id'               => $user->id,
            'first_name'       => $user->first_name,
            'last_name'        => $canViewName ? $user->last_name : null,
            'email'            => $user->email,
            'phone'            => ['number' => $user->phone, 'code' => $user->phone_country_code],
            'city'             => $user->relationLoaded('city') ? ['id' => $user->city_id, 'name' => $user->city?->name] : null,
            'avatar'           => $user->profile?->avatar_url,
            'job_title'        => $user->profile?->job_title,
            'bio'              => $user->profile?->bio,
            'sector_ids'       => $theirSectorIds,
            'services_offered' => $user->profile?->services_offered ?? [],
            'looking_for'      => $user->profile?->looking_for ?? [],
            'website'          => $user->profile?->website,
            'linkedin'         => $user->profile?->linkedin,
            'market_addressed_id' => $user->profile?->market_addressed_id,
            'market_target_id'    => $user->profile?->market_target_id,
            'presentation_video' => [
                'url' => $user->profile?->presentation_video_status === 'approved'
                    ? $user->profile?->presentation_video_url
                    : null,
                'status' => $user->profile?->presentation_video_status === 'approved' || $user->id === $currentUserId
                    ? $user->profile?->presentation_video_status
                    : null,
            ],
            'balance'          => (int) ($user->points_balance ?? 0),
            'badge'            => $this->badgePayload($user->badge_level ?? 'neutre'),
            'rating'           => $this->ratingPayload($user),
            'ambassador_status' => $user->ambassador_status ?? 'none',
            'consul_status'    => $this->deriveConsulStatus($user),
            'company'          => $user->company ? [
                'id'      => $user->company->id,
                'name'    => $user->company->name,
                'siret'   => $user->company->siret,
                'website' => $user->company->website,
                'sector'  => $user->company->sector ? ['id' => $user->company->sector->id, 'name' => $user->company->sector->name] : null,
            ] : null,
            'connection_status' => $connection?->status,
            'connection_id'     => $connection?->id,
            'i_am_sender'       => $connection ? ($connection->sender_id === $currentUserId) : false,
            'i_am_receiver'     => $connection ? ($connection->receiver_id === $currentUserId) : false,
            'is_online'         => false,
            'plan'              => $this->planPayload($user),
            'rank'              => $this->rankPayload($user),
        ];
    }

    public function getUserById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'city:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status,website,linkedin,market_addressed_id,market_target_id', 'nationality:id,name,flag', 'subscription.plan:id,name,label,max_users', 'consulRequests'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_id', 'nationality_id', 'birthday', 'phone', 'phone_country_code', 'company_id', 'points_balance', 'badge_level', 'ambassador_status', 'consul_status', 'onboarding_completed', 'created_at'])
            ->find($userId);

        if (!$user) return null;

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);
        $base['nationality'] = $user->nationality ? ['name' => $user->nationality->name, 'flag' => $user->nationality->flag] : null;
        $base['plan'] = $this->planPayload($user);
        $base['rank'] = $this->rankPayload($user);
        $base['onboarding_completed'] = (bool) ($user->onboarding_completed ?? false);
        $base['profile_completed']    = (bool) $user->hasCompletedProfile();

        return $base;
    }

    public function getProfileById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'city:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status,website,linkedin,market_addressed_id,market_target_id', 'subscription.plan:id,name,label,max_users', 'consulRequests'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_id', 'nationality_id', 'birthday', 'phone', 'phone_country_code', 'company_id', 'points_balance', 'badge_level', 'ambassador_status', 'consul_status', 'created_at'])
            ->find($userId);

        if (!$user) return null;

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);
        $base['plan'] = $this->planPayload($user);
        $base['rank'] = $this->rankPayload($user);

        $base['gender']       = $user->gender;
        $base['birthday']     = $user->birthday?->format('Y-m-d');
        $base['member_since'] = $user->created_at?->format('F Y');
        return $base;
    }

    public function planPayload(\App\Models\User $user): array
    {
        $plan = $user->effectivePlan();

        if ($plan) {
            return [
                'name'                => $plan->name,
                'label'               => $plan->label,
                'is_enterprise_owner' => $this->isEnterpriseOwnerSubscription($user->subscription),
            ];
        }

        return [
            'name'                => 'basic',
            'label'               => 'Basic',
            'is_enterprise_owner' => false,
        ];
    }

    /**
     * Compute the highest rank for a user.
     * Hierarchy: basic < premium < consul < ambassador
     */
    public function rankPayload(\App\Models\User $user): array
    {
        if ($user->ambassador_status === 'approved') {
            return ['level' => 'ambassador', 'label' => 'Ambassadeur'];
        }

        $consulStatus = $this->deriveConsulStatus($user);
        if ($consulStatus === 'approved') {
            return ['level' => 'consul', 'label' => 'Consul'];
        }

        $effectivePlan = $user->effectivePlan();
        $planName = strtolower($effectivePlan?->name ?? '');
        if ($planName && !str_contains($planName, 'basic')) {
            return ['level' => 'premium', 'label' => $effectivePlan->label ?? ucfirst($planName)];
        }

        return ['level' => 'basic', 'label' => 'Basic'];
    }

    private function isEnterpriseOwnerSubscription(?\App\Models\Subscription $subscription): bool
    {
        return $subscription !== null
            && $subscription->stripe_subscription_id !== null
            && ($subscription->plan?->max_users ?? 1) > 1;
    }

    public function getTotalUsersCount(int $currentUserId): int
    {
        return User::where('id', '!=', $currentUserId)->count();
    }

    public function ratingPayload(User $user): array
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
            ->where('accepted_at', '>=', $since)
            ->whereHas('ratings', fn($q) => $q->whereColumn('rated_at', '<=', 'leads.rating_due_at'))
            ->get(['lead_type']);

        $receivedCount = Lead::where('receiver_id', $userId)
            ->whereIn('status', [Lead::STATUS_ACCEPTED, Lead::STATUS_CONVERTED])
            ->where('accepted_at', '>=', $since)
            ->whereHas('ratings', fn($q) => $q->whereColumn('rated_at', '<=', 'leads.rating_due_at'))
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

    private function deriveConsulStatus(User $user): ?string
    {
        return $user->consul_status;
    }

    public function badgePayload(string $level): array
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
