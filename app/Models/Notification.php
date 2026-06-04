<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'body', 'data', 'is_read'];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Store a notification for a user and prune old ones (keep last 20).
     */
    public static function storeForUser(User $user, string $type, string $title, string $body, ?array $data = null): void
    {
        self::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
            'is_read' => false,
        ]);

        // Prune to last 20 per user
        $keepIds = self::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->pluck('id');

        self::where('user_id', $user->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
