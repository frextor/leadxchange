<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupInvitation extends Model
{
    protected $fillable = ['group_id', 'invited_by', 'user_id', 'status'];

    public function group(): BelongsTo  { return $this->belongsTo(Group::class); }
    public function inviter(): BelongsTo { return $this->belongsTo(User::class, 'invited_by'); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class, 'user_id'); }
}
