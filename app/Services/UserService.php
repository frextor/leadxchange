<?php

namespace App\Services;

use App\Models\User;
use App\Models\Connection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * UserService
 * 
 * Handles user-related business logic.
 * Used by both API and Web controllers (Hybrid architecture).
 */
class UserService
{
    /**
     * Get paginated users list with connection status.
     * 
     * @param int $currentUserId Current authenticated user ID
     * @param int $page Page number
     * @param string|null $search Search query
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(
        int $currentUserId,
        int $page = 1,
        ?string $search = null,
        int $perPage = 10
    ): LengthAwarePaginator {

        Log::info('UserService::getPaginatedUsers', [
            'current_user_id' => $currentUserId,
            'page' => $page,
            'search' => $search,
            'total_users' => User::count(),
        ]);

        // Build query
        $query = User::query()
            ->where('id', '!=', $currentUserId) // Exclude current user
            ->with(['company:id,name,sector']) // Eager load company
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'city_living',
                'company_id',
            ]);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('city_living', 'LIKE', "%{$search}%");
            });
        }

        // Paginate
        $users = $query->paginate($perPage, ['*'], 'page', $page);

        // Add connection status for each user
        $users->getCollection()->transform(function ($user) use ($currentUserId) {
            return $this->enrichUserWithConnectionStatus($user, $currentUserId);
        });

        return $users;
    }

    /**
     * Enrich user object with connection status.
     * 
     * @param User $user
     * @param int $currentUserId
     * @return array
     */
    public function enrichUserWithConnectionStatus(User $user, int $currentUserId): array
    {
        // Check if connection exists (bidirectional)
        $connection = Connection::where(function ($query) use ($user, $currentUserId) {
            $query->where('sender_id', $currentUserId)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user, $currentUserId) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $currentUserId);
        })->first();

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'city_living' => $user->city_living,
            'company' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'sector' => $user->company->sector,
            ] : null,

            // Connection info
            'connection_status' => $connection ? $connection->status : null,
            'connection_id' => $connection ? $connection->id : null,
            'i_am_sender' => $connection ? ($connection->sender_id === $currentUserId) : false,
            'i_am_receiver' => $connection ? ($connection->receiver_id === $currentUserId) : false,

            // Additional info (can be implemented later)
            'is_online' => false,
            'mutual_connections' => 0,
        ];
    }

    /**
     * Get single user by ID with connection status.
     * 
     * @param int $userId
     * @param int $currentUserId
     * @return array|null
     */
    public function getUserById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,sector,website'])
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'gender',
                'city_birth',
                'city_living',
                'birthday',
                'company_id',
                'created_at',
            ])
            ->find($userId);

        if (!$user) {
            return null;
        }

        return $this->enrichUserWithConnectionStatus($user, $currentUserId);
    }

    /**
     * Get full profile data for a single user (includes birthday, gender, city_birth, website).
     */
    public function getProfileById(int $userId, int $currentUserId): ?array
    {
        $user = User::with(['company:id,name,sector,website'])
            ->select(['id', 'first_name', 'last_name', 'email', 'gender', 'city_birth', 'city_living', 'birthday', 'company_id', 'created_at'])
            ->find($userId);

        if (!$user) {
            return null;
        }

        $base = $this->enrichUserWithConnectionStatus($user, $currentUserId);

        return array_merge($base, [
            'gender'       => $user->gender,
            'city_birth'   => $user->city_birth,
            'birthday'     => $user->birthday,
            'member_since' => $user->created_at?->format('F Y'),
            'company'      => $user->company ? [
                'id'      => $user->company->id,
                'name'    => $user->company->name,
                'sector'  => $user->company->sector,
                'website' => $user->company->website,
            ] : null,
        ]);
    }

    /**
     * Get total users count (excluding current user).
     *
     * @param int $currentUserId
     * @return int
     */
    public function getTotalUsersCount(int $currentUserId): int
    {
        return User::where('id', '!=', $currentUserId)->count();
    }
}
