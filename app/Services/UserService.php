<?php

namespace App\Services;

use App\Models\User;
use App\Models\Connection;
use App\Models\LeadRating;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class UserService
{
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
        array $filters = []
    ): LengthAwarePaginator {

        // Load current user's sector IDs once for shared-interest computation
        $mySectorIds = User::with('profile:user_id,sector_ids')->find($currentUserId)?->profile?->sector_ids ?? [];

        $query = User::query()
            ->where('id', '!=', $currentUserId)
            ->with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status', 'city:id,name'])
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'phone_country_code', 'city_id', 'birthday', 'gender', 'company_id', 'points_balance', 'badge_level']);

        // Basic search — name, email, city, job_title, company
        if ($search) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('first_name',  'LIKE', $like)
                  ->orWhere('last_name',  'LIKE', $like)
                  ->orWhere('email',      'LIKE', $like)
                  ->orWhereHas('city', fn ($c) => $c->where('name', 'LIKE', $like))
                  ->orWhereHas('profile',    fn ($p) => $p->where('job_title', 'LIKE', $like))
                  ->orWhereHas('company',    fn ($c) => $c->where('name', 'LIKE', $like));
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
        $applyWhere = function ($q) use ($currentUser, $search, $excludeConnectionStatuses) {
            $q->where('users.id', '!=', $currentUser->id)
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
            ->with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status', 'city:id,name'])
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

        return [
            'id'               => $user->id,
            'first_name'       => $user->first_name,
            'last_name'        => $user->last_name,
            'email'            => $user->email,
            'phone'            => ['number' => $user->phone, 'code' => $user->phone_country_code],
            'city'             => $user->relationLoaded('city') ? ['id' => $user->city_id, 'name' => $user->city?->name] : null,
            'avatar'           => $user->profile?->avatar_url,
            'job_title'        => $user->profile?->job_title,
            'bio'              => $user->profile?->bio,
            'sector_ids'       => $theirSectorIds,
            'services_offered' => $user->profile?->services_offered ?? [],
            'looking_for'      => $user->profile?->looking_for ?? [],
            'presentation_video' => [
                'url' => $user->profile?->presentation_video_status === 'approved'
                    ? $user->profile?->presentation_video_url
                    : null,
                'status' => $user->profile?->presentation_video_status === 'approved'
                    ? 'approved'
                    : null,
            ],
            'balance'          => (int) ($user->points_balance ?? 0),
            'badge'            => $this->badgePayload($user->badge_level ?? 'bronze'),
            'rating'           => $this->ratingPayload($user),
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
        ];
    }

    public function getUserById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'city:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status', 'nationality:id,name,flag', 'subscription.plan:id,name,label'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_id', 'nationality_id', 'birthday', 'phone', 'phone_country_code', 'company_id', 'points_balance', 'badge_level', 'created_at'])
            ->find($userId);

        if (!$user) return null;

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);
        $base['nationality'] = $user->nationality ? ['name' => $user->nationality->name, 'flag' => $user->nationality->flag] : null;
        $base['plan'] = $user->subscription?->plan ? [
            'name'  => $user->subscription->plan->name,
            'label' => $user->subscription->plan->label,
        ] : null;

        return $base;
    }

    public function getProfileById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,siret,sector_id,website', 'company.sector:id,name', 'city:id,name', 'profile:user_id,avatar,job_title,sector_ids,looking_for,services_offered,bio,open_to_network,presentation_video,presentation_video_status', 'subscription.plan:id,name,label'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_id', 'nationality_id', 'birthday', 'phone', 'phone_country_code', 'company_id', 'points_balance', 'badge_level', 'created_at'])
            ->find($userId);

        if (!$user) return null;

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);
        $base['plan'] = $user->subscription?->plan ? [
            'name'  => $user->subscription->plan->name,
            'label' => $user->subscription->plan->label,
        ] : null;

        $base['gender']       = $user->gender;
        $base['birthday']     = $user->birthday?->format('Y-m-d');
        $base['member_since'] = $user->created_at?->format('F Y');
        return $base;
    }

    public function getTotalUsersCount(int $currentUserId): int
    {
        return User::where('id', '!=', $currentUserId)->count();
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
