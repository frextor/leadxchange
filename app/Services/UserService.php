<?php

namespace App\Services;

use App\Models\User;
use App\Models\Connection;
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

        // Load current user's interest IDs once for shared-interest computation
        $myInterestIds = User::find($currentUserId)?->interests()->pluck('interests.id')->toArray() ?? [];

        $query = User::query()
            ->where('id', '!=', $currentUserId)
            ->with(['company:id,name,sector_id,website', 'company.sector:id,name', 'interests:id,name,icon', 'profile:user_id,avatar,job_title', 'city:id,name'])
            ->select(['id', 'first_name', 'last_name', 'email', 'city_id', 'birthday', 'gender', 'company_id', 'position']);

        // Basic search — name, email, city, position, job_title, company
        if ($search) {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('first_name',  'LIKE', $like)
                  ->orWhere('last_name',  'LIKE', $like)
                  ->orWhere('email',      'LIKE', $like)
                  ->orWhere('position',   'LIKE', $like)
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
            fn ($user) => $this->enrichUserWithConnectionStatus($user, $currentUserId, $myInterestIds)
        );

        return $users;
    }

    /**
     * Get recommended users ordered by location proximity then shared interests.
     *
     * Scoring (per user):
     *   +30 — same city as current user
     *   +10 — same profile.region
     *   +5  — per shared interest (user_interests pivot)
     */
    public function getRecommendedUsers(
        User $currentUser,
        int $page = 1,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $myCityId     = $currentUser->city_id;
        $myRegion     = $currentUser->profile?->region ?? '';
        $myInterestIds = $currentUser->interests()->pluck('interests.id')->toArray();

        if (empty($myInterestIds)) {
            $interestSql      = '0';
            $interestBindings = [];
        } else {
            $phs              = implode(',', array_fill(0, count($myInterestIds), '?'));
            $interestSql      = "(SELECT COUNT(*) FROM user_interests ui WHERE ui.user_id = users.id AND ui.interest_id IN ({$phs})) * 5";
            $interestBindings = $myInterestIds;
        }

        // Closure applied to both count and data queries
        $applyWhere = function ($q) use ($currentUser, $search) {
            $q->where('users.id', '!=', $currentUser->id);
            if ($search) {
                $like = "%{$search}%";
                $q->where(function ($q2) use ($like) {
                    $q2->where('users.first_name',   'LIKE', $like)
                       ->orWhere('users.last_name',   'LIKE', $like)
                       ->orWhere('users.position',    'LIKE', $like)
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
        $scoreBindings = array_merge([$myCityId, $myRegion], $interestBindings);

        $users = User::select('users.*')
            ->selectRaw("
                (CASE WHEN users.city_id = ? AND users.city_id IS NOT NULL THEN 30 ELSE 0 END) +
                (CASE WHEN profiles.region      = ? AND profiles.region      != ''       THEN 10 ELSE 0 END) +
                {$interestSql} as rec_score
            ", $scoreBindings)
            ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
            ->with(['company:id,name,sector_id', 'company.sector:id,name', 'interests:id,name,icon', 'profile:user_id,avatar,job_title', 'city:id,name'])
            ->tap($applyWhere)
            ->orderBy('rec_score', 'desc')
            ->orderBy('users.created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $enriched = $users->map(fn ($u) => array_merge(
            $this->enrichUserWithConnectionStatus($u, $currentUser->id, $myInterestIds),
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
     * Enrich a user with connection status and shared interests.
     */
    public function enrichUserWithConnectionStatus(
        User $user,
        int $currentUserId,
        array $myInterestIds = []
    ): array {
        $connection = Connection::where(function ($q) use ($user, $currentUserId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $currentUserId) {
            $q->where('sender_id', $user->id)->where('receiver_id', $currentUserId);
        })->first();

        // Shared interests (only when interests are eager-loaded)
        $sharedInterests = [];
        if (!empty($myInterestIds) && $user->relationLoaded('interests')) {
            $sharedInterests = $user->interests
                ->filter(fn ($i) => in_array($i->id, $myInterestIds))
                ->values()
                ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'icon' => $i->icon])
                ->toArray();
        }

        return [
            'id'               => $user->id,
            'first_name'       => $user->first_name,
            'last_name'        => $user->last_name,
            'email'            => $user->email,
            'city_id'   => $user->city_id,
            'city'      => $user->relationLoaded('city') ? $user->city?->name : null,
            'position'         => $user->position,
            'avatar'           => $user->profile?->avatar_url,
            'job_title'        => $user->profile?->job_title,
            'company'          => $user->company ? [
                'id'     => $user->company->id,
                'name'   => $user->company->name,
                'sector' => $user->company->sector?->name,
            ] : null,
            'connection_status' => $connection?->status,
            'connection_id'     => $connection?->id,
            'i_am_sender'       => $connection ? ($connection->sender_id === $currentUserId) : false,
            'i_am_receiver'     => $connection ? ($connection->receiver_id === $currentUserId) : false,
            'shared_interests'  => $sharedInterests,
            'is_online'         => false,
        ];
    }

    public function getUserById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,sector_id,website', 'company.sector:id,name', 'city:id,name'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_id', 'birthday', 'company_id', 'created_at'])
            ->find($userId);

        return $user ? $this->enrichUserWithConnectionStatus($user, $currentUserId) : null;
    }

    public function getProfileById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,sector_id,website', 'company.sector:id,name', 'city:id,name'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_id', 'birthday', 'company_id', 'created_at'])
            ->find($userId);

        if (!$user) return null;

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);

        return array_merge($base, [
            'gender'       => $user->gender,
            'birthday'     => $user->birthday,
            'member_since' => $user->created_at?->format('F Y'),
            'company'      => $user->company ? [
                'id'      => $user->company->id,
                'name'    => $user->company->name,
                'sector'  => $user->company->sector?->name,
                'website' => $user->company->website,
            ] : null,
        ]);
    }

    public function getTotalUsersCount(int $currentUserId): int
    {
        return User::where('id', '!=', $currentUserId)->count();
    }
}
