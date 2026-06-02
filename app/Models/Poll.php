<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    protected $fillable = ['group_id', 'user_id', 'question', 'is_multiple_choice', 'ends_at'];
    protected $casts = ['is_multiple_choice' => 'boolean', 'ends_at' => 'datetime'];

    public function group() { return $this->belongsTo(Group::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function options() { return $this->hasMany(PollOption::class); }
    public function votes() { return $this->hasMany(PollVote::class); }
}
