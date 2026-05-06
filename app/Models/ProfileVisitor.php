<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVisitor extends Model
{
    protected $table = 'profile_visitors';

    protected $fillable = [
        'profile_user_id',
        'visitor_id',
        'visit_count',
        'last_visited_at',
        'is_new',
    ];

    protected $casts = [
        'last_visited_at' => 'datetime',
        'is_new'          => 'boolean',
    ];

    public function profileUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_user_id');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }
}
