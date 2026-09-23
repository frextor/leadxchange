<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

/**
 * Compteurs affichés dans la navigation (badges Leads / Chat / Notifications).
 * Partagé par layouts/app.blade.php et layouts/app2.blade.php — calculé une seule fois par requête.
 */
class NavCounts
{
    protected static ?array $cache = null;

    public static function forCurrentUser(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $authId = auth()->id();

        if (! $authId) {
            return static::$cache = ['pendingLeadsCount' => 0, 'unreadChatCount' => 0, 'unreadNotifCount' => 0];
        }

        return static::$cache = [
            'pendingLeadsCount' => DB::table('leads')
                ->where('receiver_id', $authId)
                ->where('status', Lead::STATUS_NEW)
                ->count(),

            'unreadChatCount' => DB::table('messages')
                ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
                ->where(function ($q) use ($authId) {
                    $q->where('conversations.user1_id', $authId)->orWhere('conversations.user2_id', $authId);
                })
                ->where('messages.sender_id', '!=', $authId)
                ->whereNull('messages.read_at')
                ->count(),

            'unreadNotifCount' => DB::table('notifications')
                ->where('user_id', $authId)
                ->where('is_read', false)
                ->count(),
        ];
    }
}
