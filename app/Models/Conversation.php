<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['user1_id', 'user2_id', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function otherUser(int $currentUserId): ?User
    {
        return $currentUserId === $this->user1_id ? $this->user2 : $this->user1;
    }

    public function unreadCount(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Find or create a conversation between two users.
     * user1_id is always the smaller ID to enforce the unique constraint.
     */
    public static function between(int $a, int $b): self
    {
        [$u1, $u2] = $a < $b ? [$a, $b] : [$b, $a];

        return self::firstOrCreate(
            ['user1_id' => $u1, 'user2_id' => $u2],
            ['last_message_at' => null]
        );
    }
}
