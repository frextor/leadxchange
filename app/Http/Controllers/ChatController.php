<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user       = $request->user();
        $withUserId = (int) $request->query('with', 0);

        $conversations = Conversation::with(['user1', 'user2', 'lastMessage'])
            ->where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn($c) => [
                'conv'   => $c,
                'other'  => $c->otherUser($user->id),
                'unread' => $c->unreadCount($user->id),
            ]);

        $otherUser    = null;
        $messages     = collect();
        $conversation = null;

        if ($withUserId) {
            abort_unless($user->isConnectedWith($withUserId), 403, 'You must be connected to start a conversation.');

            $otherUser    = User::with(['company', 'profile'])->findOrFail($withUserId);
            $conversation = Conversation::between($user->id, $withUserId);
            $messages     = $conversation->messages()->get();

            // Mark incoming messages as read
            $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

        } elseif ($conversations->isNotEmpty()) {
            return redirect()->route('chat.index', ['with' => $conversations->first()['other']->id]);
        }

        $connectionIds = $user->connectionIds();
        $connections   = User::with('company')
            ->whereIn('id', $connectionIds)
            ->select('id', 'first_name', 'last_name', 'company_id')
            ->orderBy('first_name')
            ->get();

        return view('chat.index', compact('conversations', 'otherUser', 'messages', 'conversation', 'connections'));
    }

    public function store(Request $request, int $userId)
    {
        $request->validate(['body' => ['required', 'string', 'max:3000']]);

        $sender = $request->user();

        if (!$sender->isConnectedWith($userId)) {
            return response()->json(['error' => 'Not connected.'], 403);
        }

        $conversation = Conversation::between($sender->id, $userId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'body'            => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'id'         => $message->id,
            'sender_id'  => $message->sender_id,
            'body'       => $message->body,
            'created_at' => $message->created_at->toIso8601String(),
            'is_mine'    => true,
        ]);
    }

    public function poll(Request $request, int $userId, int $lastId)
    {
        $user         = $request->user();
        $conversation = Conversation::between($user->id, $userId);

        $messages = $conversation->messages()
            ->where('id', '>', $lastId)
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'sender_id'  => $m->sender_id,
                'body'       => $m->body,
                'created_at' => $m->created_at->toIso8601String(),
                'is_mine'    => $m->sender_id === $user->id,
            ]);

        // Mark incoming as read
        if ($messages->where('is_mine', false)->isNotEmpty()) {
            $conversation->messages()
                ->where('id', '>', $lastId)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $otherTyping = Cache::has("typing:{$userId}:{$user->id}");

        return response()->json([
            'messages'     => $messages->values(),
            'other_typing' => $otherTyping,
        ]);
    }

    public function typing(Request $request, int $userId): \Illuminate\Http\JsonResponse
    {
        Cache::put("typing:{$request->user()->id}:{$userId}", true, now()->addSeconds(5));
        return response()->json(['ok' => true]);
    }
}
