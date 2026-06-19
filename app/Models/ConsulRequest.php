<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsulRequest extends Model
{
    protected $fillable = ['user_id', 'status', 'validated_by', 'validated_at', 'rejection_reason'];

    protected $casts = ['validated_at' => 'datetime'];

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function user()        { return $this->belongsTo(User::class); }
    public function validator()   { return $this->belongsTo(User::class, 'validated_by'); }

    public function isPending(): bool  { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_APPROVED; }
    public function isRejected(): bool { return $this->status === self::STATUS_REJECTED; }
}
