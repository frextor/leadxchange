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
     * @param array       $filters Advanced filters: gender, age_min, age_max, city_birth,
     *                             city_living, company, interests (array of IDs), open_to_network
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
            ->with(['company:id,name,sector_id,website', 'company.sector:id,name', 'interests:id,name,icon'])
            ->select(['id', 'first_name', 'last_name', 'email', 'city_living', 'city_birth', 'birthday', 'gender', 'company_id']);

        // Basic search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name',  'LIKE', "%{$search}%")
                  ->orWhere('email',      'LIKE', "%{$search}%")
                  ->orWhere('city_living','LIKE', "%{$search}%");
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

        // City of birth
        if (!empty($filters['city_birth'])) {
            $query->where('city_birth', 'LIKE', '%' . $filters['city_birth'] . '%');
        }

        // City of living (advanced filter, distinct from basic search)
        if (!empty($filters['city_living'])) {
            $query->where('city_living', 'LIKE', '%' . $filters['city_living'] . '%');
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
            'city_living'      => $user->city_living,
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
        $user = User::with(['company:id,name,sector_id,website', 'company.sector:id,name'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_birth', 'city_living', 'birthday', 'company_id', 'created_at'])
            ->find($userId);

        return $user ? $this->enrichUserWithConnectionStatus($user, $currentUserId) : null;
    }

    public function getProfileById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,sector_id,website', 'company.sector:id,name'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_birth', 'city_living', 'birthday', 'company_id', 'created_at'])
            ->find($userId);

        if (!$user) return null;

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);

        return array_merge($base, [
            'gender'       => $user->gender,
            'city_birth'   => $user->city_birth,
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
