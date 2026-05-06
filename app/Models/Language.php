<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'native_name', 'code'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_languages')
                    ->withPivot('level')
                    ->withTimestamps();
    }
}
