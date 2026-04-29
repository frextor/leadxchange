<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\User;
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
                throw new \Exception('This connection was previously rejected');
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

            Log::info('Connection request sent', [
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'connection_id' => $connection->id
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
    public function getReceivedRequests(User $user, ?string $status = null): Collection
    {
        $query = Connection::where('receiver_id', $user->id)
            ->with(['sender' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'company_id');
            }]);

        if ($status) {
            $query->where('status', $status);
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
    public function getSentRequests(User $user, ?string $status = null): Collection
    {
        $query = Connection::where('sender_id', $user->id)
            ->with(['receiver' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'company_id');
            }]);

        if ($status) {
            $query->where('status', $status);
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
        $asSender = Connection::where('sender_id', $user->id)
            ->where('status', Connection::STATUS_ACCEPTED)
            ->with(['receiver' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'company_id');
            }])
            ->get();

        $asReceiver = Connection::where('receiver_id', $user->id)
            ->where('status', Connection::STATUS_ACCEPTED)
            ->with(['sender' => function ($query) {
                $query->select('id', 'first_name', 'last_name', 'email', 'company_id');
            }])
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

        // Only sender can cancel
        if ($connection->sender_id !== $user->id) {
            throw new \Exception('Only the sender can cancel this connection request');
        }

        // Must be pending
        if (!$connection->isPending()) {
            throw new \Exception('Only pending requests can be cancelled');
        }

        return $connection->delete();
    }
}
