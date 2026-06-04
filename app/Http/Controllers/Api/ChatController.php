<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * List all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::with(['user1', 'user2', 'lastMessage'])
            ->where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($c) use ($user) {
                $other = $c->otherUser($user->id);
                return [
                    'id'              => $c->id,
                    'last_message_at' => $c->last_message_at?->toIso8601String(),
                    'unread'          => $c->unreadCount($user->id),
                    'last_message'    => $c->lastMessage ? [
                        'body'       => $c->lastMessage->body,
                        'is_mine'    => $c->lastMessage->sender_id === $user->id,
                        'created_at' => $c->lastMessage->created_at->toIso8601String(),
                    ] : null,
                    'other_user' => [
                        'id'       => $other->id,
                        'name'     => $other->full_name,
                        'job_title' => $other->profile?->job_title,
                        'company'  => $other->company?->name,
                        'avatar'   => $other->profile?->avatar_url,
                    ],
                ];
            });

        return response()->json([
            'data' => $conversations->values(),
            'meta' => ['total' => $conversations->count()],
        ]);
    }

    /**
     * Get messages for the conversation with a specific user.
     */
    public function show(Request $request, int $userId): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->isConnectedWith($userId), 403, 'Not connected.');

        $otherUser    = User::findOrFail($userId);
        $conversation = Conversation::between($user->id, $userId);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => $this->formatMessage($m, $user->id));

        // Mark as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'other_user' => [
                    'id'       => $otherUser->id,
                    'name'     => $otherUser->full_name,
                    'job_title' => $otherUser->profile?->job_title,
                    'company'  => $otherUser->company?->name,
                    'avatar'   => $otherUser->profile?->avatar_url,
                ],
                'messages' => $messages->values(),
            ],
        ]);
    }

    /**
     * Send a message to a user.
     */
    public function store(Request $request, int $userId): JsonResponse
    {
        $request->validate(['body' => ['required', 'string', 'max:3000']]);

        $sender = $request->user();

        abort_unless($sender->isConnectedWith($userId), 403, 'Not connected.');

        $conversation = Conversation::between($sender->id, $userId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'body'            => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'message' => $this->formatMessage($message, $sender->id),
        ], 201);
    }

    /**
     * Poll for new messages since a given message ID.
     */
    public function poll(Request $request, int $userId, int $lastId): JsonResponse
    {
        $user         = $request->user();
        $conversation = Conversation::between($user->id, $userId);

        $messages = $conversation->messages()
            ->where('id', '>', $lastId)
            ->get()
            ->map(fn($m) => $this->formatMessage($m, $user->id));

        if ($messages->where('is_mine', false)->isNotEmpty()) {
            $conversation->messages()
                ->where('id', '>', $lastId)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json(['messages' => $messages->values()]);
    }

    private function formatMessage(Message $message, int $currentUserId): array
    {
        return [
            'id'         => $message->id,
            'sender_id'  => $message->sender_id,
            'body'       => $message->body,
            'is_mine'    => $message->sender_id === $currentUserId,
            'read_at'    => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at->toIso8601String(),
        ];
    }
}
