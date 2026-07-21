<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ConnectionService
 * 
 * Handles all connection/networking business logic.
 */
class ConnectionService
{
    /**
     * Send a connection request.
     *
     * @param User $sender
     * @param int $receiverId
     * @return Connection
     * @throws \Exception
     */
    public function sendRequest(User $sender, int $receiverId): Connection
    {
        // Rule 1: Cannot connect to yourself
        if ($sender->id === $receiverId) {
            throw new \Exception('You cannot send a connection request to yourself');
        }

        // Check if receiver exists
        $receiver = User::find($receiverId);
        if (!$receiver) {
            throw new \Exception('User not found');
        }

        // Rule 2: No duplicate requests (check both directions)
        $existingConnection = Connection::where(function ($query) use ($sender, $receiverId) {
            $query->where('sender_id', $sender->id)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($sender, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $sender->id);
        })->first();

        if ($existingConnection) {
            if ($existingConnection->isPending()) {
                throw new \Exception('A connection request already exists between these users');
            }
            if ($existingConnection->isAccepted()) {
                throw new \Exception('You are already connected with this user');
            }
            if ($existingConnection->isRejected()) {
                // Allow re-sending after rejection: reset to pending
                $existingConnection->update([
                    'status' => Connection::STATUS_PENDING,
                    'sender_id' => $sender->id,
                    'receiver_id' => $receiverId,
                ]);
                $refreshed = $existingConnection->fresh();
                try {
                    app(FirebaseService::class)->sendConnectionNotification($refreshed, $sender);
                } catch (\Exception $e) {
                    Log::warning('Firebase notification failed (re-send after rejection)', ['error' => $e->getMessage()]);
                }
                return $refreshed;
            }
        }

        // Rule 3: Monthly connection quota from plan (admin-configurable via permissions)
        $quota = $sender->planPermission('max_connections_per_month');
        if (is_int($quota) && $quota > 0) {
            $sentThisMonth = Connection::where('sender_id', $sender->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
            if ($sentThisMonth >= $quota) {
                throw new \Exception(
                    "Limite atteinte : votre plan autorise {$quota} demande(s) de connexion par mois. Passez à un plan supérieur pour continuer."
                );
            }
        }

        DB::beginTransaction();

        try {
            $connection = Connection::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'status' => Connection::STATUS_PENDING,
            ]);

            DB::commit();

            try {
                app(FirebaseService::class)->sendConnectionNotification($connection, $sender);
            } catch (\Exception $e) {
                Log::warning('Firebase notification failed', ['error' => $e->getMessage()]);
            }

            Log::info('Connection request sent', [
                'sender_id'     => $sender->id,
                'receiver_id'   => $receiverId,
                'connection_id' => $connection->id,
            ]);

            return $connection;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to send connection request', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Accept a connection request.
     *
     * @param int $connectionId
     * @param User $user
     * @return Connection
     * @throws \Exception
     */
    public function acceptRequest(int $connectionId, User $user): Connection
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            throw new \Exception('Connection request not found');
        }

        // Rule 3: Only receiver can accept
        if ($connection->receiver_id !== $user->id) {
            throw new \Exception('Only the receiver can accept this connection request');
        }

        // Must be pending
        if (!$connection->isPending()) {
            throw new \Exception('This connection request cannot be accepted (status: ' . $connection->status . ')');
        }

        DB::beginTransaction();

        try {
            $connection->update([
                'status' => Connection::STATUS_ACCEPTED,
            ]);

            DB::commit();

            try {
                app(FirebaseService::class)->sendConnectionAcceptedNotification($connection, $user);
            } catch (\Exception $e) {
                Log::warning('Firebase notification failed (connection accepted)', ['error' => $e->getMessage()]);
            }

            Log::info('Connection request accepted', [
                'connection_id' => $connection->id,
                'sender_id' => $connection->sender_id,
                'receiver_id' => $connection->receiver_id
            ]);

            return $connection->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept connection request', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reject a connection request.
     *
     * @param int $connectionId
     * @param User $user
     * @return Connection
     * @throws \Exception
     */
    public function rejectRequest(int $connectionId, User $user): Connection
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            throw new \Exception('Connection request not found');
        }

        // Rule 3: Only receiver can reject
        if ($connection->receiver_id !== $user->id) {
            throw new \Exception('Only the receiver can reject this connection request');
        }

        // Must be pending
        if (!$connection->isPending()) {
            throw new \Exception('This connection request cannot be rejected (status: ' . $connection->status . ')');
        }

        DB::beginTransaction();

        try {
            $connection->update([
                'status' => Connection::STATUS_REJECTED,
            ]);

            DB::commit();

            Log::info('Connection request rejected', [
                'connection_id' => $connection->id,
                'sender_id' => $connection->sender_id,
                'receiver_id' => $connection->receiver_id
            ]);

            return $connection->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reject connection request', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get connection requests received by user.
     *
     * @param User $user
     * @param string|null $status
     * @return Collection
     */
    public function getReceivedRequests(User $user, ?string $status = null, ?int $groupId = null): Collection
    {
        $query = Connection::where('receiver_id', $user->id)
            ->whereHas('sender', fn($q) => $q->regular())
            ->with(['sender' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'company_id');
            }, 'sender.profile:user_id,avatar']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($groupId) {
            $query->whereNotIn('sender_id', function ($sub) use ($groupId) {
                $sub->select('user_id')->from('group_user')->where('group_id', $groupId)->whereNull('blocked_at');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get connection requests sent by user.
     *
     * @param User $user
     * @param string|null $status
     * @return Collection
     */
    public function getSentRequests(User $user, ?string $status = null, ?int $groupId = null): Collection
    {
        $query = Connection::where('sender_id', $user->id)
            ->whereHas('receiver', fn($q) => $q->regular())
            ->with(['receiver' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'company_id');
            }, 'receiver.profile:user_id,avatar']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($groupId) {
            $query->whereNotIn('receiver_id', function ($sub) use ($groupId) {
                $sub->select('user_id')->from('group_user')->where('group_id', $groupId)->whereNull('blocked_at');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all connections (accepted) for a user.
     *
     * @param User $user
     * @return Collection
     */
    public function getConnections(User $user): Collection
    {
        // Get connections where user is either sender or receiver and status is accepted
        $userRelations = [
            'profile:user_id,avatar,job_title',
            'company:id,name',
            'city:id,name',
            'subscription.plan:id,name,label',
        ];

        $asSender = Connection::where('sender_id', $user->id)
            ->where('status', Connection::STATUS_ACCEPTED)
            ->whereHas('receiver', fn($q) => $q->regular())
            ->with(array_merge(
                ['receiver' => fn($q) => $q->select('id', 'first_name', 'last_name', 'email', 'company_id', 'city_id', 'badge_level', 'ambassador_status', 'consul_status', 'points_balance')],
                array_map(fn($r) => 'receiver.' . $r, $userRelations)
            ))
            ->get();

        $asReceiver = Connection::where('receiver_id', $user->id)
            ->where('status', Connection::STATUS_ACCEPTED)
            ->whereHas('sender', fn($q) => $q->regular())
            ->with(array_merge(
                ['sender' => fn($q) => $q->select('id', 'first_name', 'last_name', 'email', 'company_id', 'city_id', 'badge_level', 'ambassador_status', 'consul_status', 'points_balance')],
                array_map(fn($r) => 'sender.' . $r, $userRelations)
            ))
            ->get();

        return $asSender->merge($asReceiver)->sortByDesc('created_at');
    }

    /**
     * Check if two users are connected.
     *
     * @param User $user1
     * @param User $user2
     * @return bool
     */
    public function areConnected(User $user1, User $user2): bool
    {
        return Connection::where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                  ->where('receiver_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)
                  ->where('receiver_id', $user1->id);
        })->where('status', Connection::STATUS_ACCEPTED)
          ->exists();
    }

    /**
     * Remove an accepted connection (either party can remove).
     */
    public function removeConnection(int $connectionId, User $user): bool
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            throw new \Exception('Connection not found');
        }

        if ($connection->sender_id !== $user->id && $connection->receiver_id !== $user->id) {
            throw new \Exception('You are not part of this connection');
        }

        if ($connection->status !== 'accepted') {
            throw new \Exception('Only accepted connections can be removed');
        }

        return $connection->delete();
    }

    /**
     * Cancel a pending sent request.
     *
     * @param int $connectionId
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function cancelRequest(int $connectionId, User $user): bool
    {
        $connection = Connection::find($connectionId);

        if (!$connection) {
            throw new \Exception('Connection request not found');
        }

        // Must be sender or receiver
        if ($connection->sender_id !== $user->id && $connection->receiver_id !== $user->id) {
            throw new \Exception('You are not part of this connection');
        }

        return $connection->delete();
    }
}
