<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ConnectionController (API)
 * 
 * Thin controller - uses ConnectionService for business logic.
 */
class ConnectionController extends Controller
{
    protected ConnectionService $connectionService;

    public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * Send a connection request.
     * 
     * POST /api/connections
     * Body: { "receiver_id": 5 }
     */
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->canFeature('can_send_invitations')) {
            return response()->json([
                'message' => 'Votre plan ne permet pas d\'envoyer des invitations.',
                'upgrade' => true,
            ], 403);
        }

        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $connection = $this->connectionService->sendRequest(
                $request->user(),
                $validated['receiver_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Connection request sent successfully',
                'data' => [
                    'connection' => [
                        'id' => $connection->id,
                        'receiver_id' => $connection->receiver_id,
                        'status' => $connection->status,
                        'created_at' => $connection->created_at,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all connections (sent, received, accepted).
     * 
     * GET /api/connections?type=received&status=pending
     */
    public function index(Request $request): JsonResponse
    {
        $type    = $request->get('type', 'all'); // all, sent, received, connections
        $status  = $request->get('status'); // pending, accepted, rejected
        $groupId = $request->integer('group_id') ?: null;

        try {
            switch ($type) {
                case 'received':
                    $connections = $this->connectionService->getReceivedRequests(
                        $request->user(),
                        $status,
                        $groupId
                    );
                    break;

                case 'sent':
                    $connections = $this->connectionService->getSentRequests(
                        $request->user(),
                        $status,
                        $groupId
                    );
                    break;

                case 'connections':
                    $connections = $this->connectionService->getConnections(
                        $request->user()
                    );
                    break;

                default: // 'all'
                    $received = $this->connectionService->getReceivedRequests($request->user(), $status, $groupId);
                    $sent     = $this->connectionService->getSentRequests($request->user(), $status, $groupId);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'received' => $this->formatConnections($received),
                            'sent' => $this->formatConnections($sent),
                        ]
                    ]);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatConnections($connections)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve connections',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept a connection request.
     * 
     * POST /api/connections/{id}/accept
     */
    public function accept(int $id, Request $request): JsonResponse
    {
        try {
            $connection = $this->connectionService->acceptRequest($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Connection request accepted',
                'data' => [
                    'connection' => [
                        'id' => $connection->id,
                        'sender_id' => $connection->sender_id,
                        'receiver_id' => $connection->receiver_id,
                        'status' => $connection->status,
                        'updated_at' => $connection->updated_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject a connection request.
     * 
     * POST /api/connections/{id}/reject
     */
    public function reject(int $id, Request $request): JsonResponse
    {
        try {
            $connection = $this->connectionService->rejectRequest($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Connection request rejected',
                'data' => [
                    'connection' => [
                        'id' => $connection->id,
                        'sender_id' => $connection->sender_id,
                        'receiver_id' => $connection->receiver_id,
                        'status' => $connection->status,
                        'updated_at' => $connection->updated_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a sent connection request.
     * 
     * DELETE /api/connections/{id}
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        try {
            $this->connectionService->cancelRequest($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Connection request cancelled',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove an accepted connection.
     *
     * POST /api/connections/{id}/remove
     */
    public function remove(int $id, Request $request): JsonResponse
    {
        try {
            $this->connectionService->removeConnection($id, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Connection removed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Format connections for response.
     */
    private function formatConnections($connections): array
    {
        return $connections->map(function ($connection) {
            $otherUser = $connection->sender_id === auth()->id()
                ? $connection->receiver
                : $connection->sender;

            return [
                'id' => $connection->id,
                'user' => $otherUser ? [
                    'id' => $otherUser->id,
                    'first_name' => $otherUser->first_name,
                    'last_name' => $otherUser->last_name,
                    'email' => $otherUser->email,
                    'avatar' => $otherUser->profile?->avatar_url,
                    'avatar_url' => $otherUser->profile?->avatar_url,
                ] : null,
                'status' => $connection->status,
                'type' => $connection->sender_id === auth()->id() ? 'sent' : 'received',
                'created_at' => $connection->created_at,
                'updated_at' => $connection->updated_at,
            ];
        })->values()->toArray();
    }
}
