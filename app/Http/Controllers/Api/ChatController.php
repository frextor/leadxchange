<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function __construct(private readonly FirebaseService $firebaseService) {}

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
                if (!$other) return null;
                return [
                    'id'              => $c->id,
                    'last_message_at' => $c->last_message_at?->toIso8601String(),
                    'unread'          => $c->unreadCount($user->id),
                    'unread_count'    => $c->unreadCount($user->id),
                    'last_message'    => $c->lastMessage ? [
                        'type'       => $c->lastMessage->type,
                        'preview'    => $this->messagePreview($c->lastMessage),
                        'body'       => $c->lastMessage->body,
                        'is_mine'    => $c->lastMessage->sender_id === $user->id,
                        'created_at' => $c->lastMessage->created_at->toIso8601String(),
                    ] : null,
                    'other_user' => [
                        'id'        => $other->id,
                        'name'      => $other->full_name,
                        'job_title' => $other->profile?->job_title,
                        'company'   => $other->company?->name,
                        'avatar'    => $other->profile?->avatar_url,
                    ],
                ];
            });

        $conversations = $conversations->filter()->values();

        return response()->json([
            'data' => $conversations,
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

        // Mark incoming messages as read
        $updated = $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Reset unread count in Firebase inbox
        if ($updated > 0) {
            try {
                $this->firebaseService->markChatRead($conversation, $user->id);
            } catch (\Throwable $e) {
                Log::warning('Chat Firebase markRead failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
                'other_user' => [
                    'id'        => $otherUser->id,
                    'name'      => $otherUser->full_name,
                    'job_title' => $otherUser->profile?->job_title,
                    'company'   => $otherUser->company?->name,
                    'avatar'    => $otherUser->profile?->avatar_url,
                ],
                'messages' => $messages->values(),
            ],
        ]);
    }

    /**
     * Send a message (text, image, or audio) to a user.
     */
    public function store(Request $request, int $userId): JsonResponse
    {
        $sender = $request->user();

        abort_if($sender->id === $userId, 422, 'Cannot send a message to yourself.');
        abort_unless($sender->isConnectedWith($userId), 403, 'Not connected.');

        $type = $request->input('type', 'text');

        $this->validateByType($request, $type);

        $conversation = Conversation::between($sender->id, $userId);

        // Idempotency: return existing message if client_message_id matches
        $clientId = $request->input('client_message_id');
        if ($clientId) {
            $existing = Message::where('conversation_id', $conversation->id)
                ->where('client_message_id', $clientId)
                ->first();
            if ($existing) {
                $payload = $this->formatMessage($existing, $sender->id);
                return response()->json(['data' => $payload, 'message' => $payload], 201);
            }
        }

        $data = [
            'conversation_id'   => $conversation->id,
            'sender_id'         => $sender->id,
            'receiver_id'       => $userId,
            'type'              => $type,
            'client_message_id' => $clientId ?: null,
        ];

        if ($type === 'text') {
            $data['body'] = $request->input('body');
        } elseif ($type === 'image') {
            $path = $request->file('image')->store("chat/conversations/{$conversation->id}/images", 'public');
            $data['media_url'] = asset('storage/' . $path);
            $data['caption']   = $request->input('caption');
        } elseif ($type === 'audio') {
            $file              = $request->file('audio');
            $path              = $file->store("chat/conversations/{$conversation->id}/audio", 'public');
            $data['media_url']   = asset('storage/' . $path);
            $data['filename']    = $file->getClientOriginalName();
            $data['duration_ms'] = $request->integer('duration_ms') ?: null;
        }

        $message = Message::create($data);
        $conversation->update(['last_message_at' => now()]);

        // Firebase RTDB sync + FCM notification (best-effort)
        try {
            $this->firebaseService->syncChatMessage($message, $conversation);
            $receiver = User::find($userId);
            if ($receiver) {
                $this->firebaseService->sendChatNotification($message, $sender, $receiver);
            }
        } catch (\Throwable $e) {
            Log::warning('Chat Firebase sync failed', ['error' => $e->getMessage()]);
        }

        $payload = $this->formatMessage($message, $sender->id);
        return response()->json(['data' => $payload, 'message' => $payload], 201);
    }

    /**
     * Mark all unread messages in a conversation as read.
     */
    public function markRead(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $conversationId)
            ->where(fn($q) => $q->where('user1_id', $user->id)->orWhere('user2_id', $user->id))
            ->firstOrFail();

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        try {
            $this->firebaseService->markChatRead($conversation, $user->id);
        } catch (\Throwable $e) {
            Log::warning('Chat Firebase markRead failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Update typing indicator for a conversation.
     */
    public function typing(Request $request, int $conversationId): JsonResponse
    {
        $user     = $request->user();
        $isTyping = $request->boolean('is_typing', true);

        $conversation = Conversation::where('id', $conversationId)
            ->where(fn($q) => $q->where('user1_id', $user->id)->orWhere('user2_id', $user->id))
            ->firstOrFail();

        try {
            $this->firebaseService->setChatTyping($conversation, $user->id, $isTyping);
        } catch (\Throwable) {
            // Typing failures are non-critical — silently ignore
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Poll for new messages since a given message ID.
     */
    public function poll(Request $request, int $userId, int $lastId): JsonResponse
    {
        $user         = $request->user();

        abort_if($user->id === $userId, 422, 'Cannot poll a conversation with yourself.');
        abort_unless($user->isConnectedWith($userId), 403, 'Not connected.');

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
            'id'                => $message->id,
            'client_message_id' => $message->client_message_id,
            'conversation_id'   => $message->conversation_id,
            'sender_id'         => $message->sender_id,
            'receiver_id'       => $message->receiver_id,
            'type'              => $message->type,
            'body'              => $message->body,
            'media_url'         => $message->media_url,
            'caption'           => $message->caption,
            'filename'          => $message->filename,
            'duration_ms'       => $message->duration_ms,
            'is_mine'           => $message->sender_id === $currentUserId,
            'read_at'           => $message->read_at?->toIso8601String(),
            'created_at'        => $message->created_at->toIso8601String(),
        ];
    }

    private function messagePreview(Message $message): string
    {
        return match ($message->type) {
            'image' => $message->caption ?: 'Image',
            'audio' => 'Audio message',
            default => $message->body ?? '',
        };
    }

    private function validateByType(Request $request, string $type): void
    {
        match ($type) {
            'text' => $request->validate([
                'body'              => ['required', 'string', 'max:3000'],
                'client_message_id' => ['nullable', 'string', 'max:80'],
            ]),
            'image' => $request->validate([
                'image'             => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                'caption'           => ['nullable', 'string', 'max:1000'],
                'client_message_id' => ['nullable', 'string', 'max:80'],
            ]),
            'audio' => $request->validate([
                'audio'             => ['required', 'file', 'mimes:mp3,wav,ogg,opus,m4a,aac', 'max:16384'],
                'duration_ms'       => ['nullable', 'integer', 'min:0'],
                'client_message_id' => ['nullable', 'string', 'max:80'],
            ]),
            default => abort(422, 'Invalid message type. Allowed: text, image, audio.'),
        };
    }
}
