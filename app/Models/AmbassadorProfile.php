<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbassadorProfile extends Model
{
    protected $fillable = [
        'user_id', 'biography', 'linkedin_url',
        'score', 'national_rank', 'regional_rank', 'achievements',
    ];

    protected $casts = [
        'achievements' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedAchievements(): array
    {
        $earned = $this->achievements ?? [];
        return array_filter(self::ALL_ACHIEVEMENTS, fn ($a) => in_array($a['key'], $earned));
    }

    public const ALL_ACHIEVEMENTS = [
        ['key' => 'bronze',    'label' => 'Bronze Ambassador',    'emoji' => '🥉', 'threshold' => 100],
        ['key' => 'silver',    'label' => 'Silver Ambassador',    'emoji' => '🥈', 'threshold' => 500],
        ['key' => 'gold',      'label' => 'Gold Ambassador',      'emoji' => '🥇', 'threshold' => 1000],
        ['key' => 'top',       'label' => 'Top Ambassador',       'emoji' => '🏆', 'threshold' => 2000],
        ['key' => 'networker', 'label' => 'Networking Expert',    'emoji' => '⭐', 'threshold' => 3000],
    ];
}
