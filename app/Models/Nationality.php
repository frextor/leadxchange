<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $fillable = ['name', 'country', 'code', 'flag'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
