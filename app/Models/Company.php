<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'siret',
        'sector_id',
        'website',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getSectorNameAttribute(): ?string
    {
        return $this->sector?->name;
    }
}
