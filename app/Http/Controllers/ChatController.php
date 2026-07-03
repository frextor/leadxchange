<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user       = $request->user();
        $withUserId = (int) $request->query('with', 0);

        // Only show conversations with accepted connections
        $connectedIds = $user->connectionIds();

        $conversations = Conversation::with(['user1', 'user2', 'lastMessage'])
            ->where(function ($q) use ($user, $connectedIds) {
                $q->where('user1_id', $user->id)->whereIn('user2_id', $connectedIds);
            })
            ->orWhere(function ($q) use ($user, $connectedIds) {
                $q->where('user2_id', $user->id)->whereIn('user1_id', $connectedIds);
            })
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
            $messages     = $conversation->messages()->oldest()->get();

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

        $canChat = $user->canFeature('can_receive_mail');

        return view('chat.index', compact('conversations', 'otherUser', 'messages', 'conversation', 'connections', 'canChat'));
    }

    public function store(Request $request, int $userId)
    {
        $request->validate(['body' => ['required_without:media_url', 'nullable', 'string', 'max:3000']]);

        $sender = $request->user();

        if (!$sender->isConnectedWith($userId)) {
            return response()->json(['error' => 'Not connected.'], 403);
        }

        $conversation = Conversation::between($sender->id, $userId);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'type'            => $request->input('type', 'text'),
            'body'            => $request->body,
            'media_url'       => $request->input('media_url'),
            'filename'        => $request->input('filename'),
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'id'         => $message->id,
            'sender_id'  => $message->sender_id,
            'type'       => $message->type,
            'body'       => $message->body,
            'media_url'  => $message->media_url,
            'filename'   => $message->filename,
            'created_at' => $message->created_at->toIso8601String(),
            'is_mine'    => true,
        ]);
    }

    public function uploadMedia(Request $request, int $userId): \Illuminate\Http\JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx']]);

        $sender = $request->user();
        if (!$sender->isConnectedWith($userId)) {
            return response()->json(['error' => 'Not connected.'], 403);
        }

        $path = $request->file('file')->store('chat/media', 'public');
        $url  = Storage::disk('public')->url($path);
        $mime = $request->file('file')->getMimeType();
        $type = str_starts_with($mime, 'image/') ? 'image' : 'file';

        return response()->json(['url' => $url, 'type' => $type, 'filename' => $request->file('file')->getClientOriginalName()]);
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
                'type'       => $m->type ?? 'text',
                'body'       => $m->body,
                'media_url'  => $m->media_url,
                'filename'   => $m->filename,
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

    /**
     * Check for new/updated conversations (used to refresh the sidebar).
     * Returns conversations with unread messages + their last message ID.
     */
    public function checkInbox(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::with(['lastMessage'])
            ->where(function ($q) use ($user) {
                $q->where('user1_id', $user->id)
                  ->orWhere('user2_id', $user->id);
            })
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn ($c) => [
                'id'          => $c->id,
                'other_id'    => $c->otherUser($user->id)?->id,
                'unread'      => $c->unreadCount($user->id),
                'last_msg_id' => $c->lastMessage?->id ?? 0,
                'updated_at'  => $c->last_message_at?->toIso8601String(),
            ]);

        return response()->json([
            'conversations' => $conversations->values(),
            'total_unread'  => $conversations->sum('unread'),
        ]);
    }
}
