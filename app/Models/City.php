<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'country_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Résout un nom de ville (saisi librement, ex. à l'inscription) vers une City existante
     * (comparaison insensible à la casse/aux espaces) ou en crée une nouvelle à la volée.
     */
    public static function findOrCreateByName(string $name): self
    {
        $name = trim($name);

        return static::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? static::create(['name' => $name, 'is_active' => true]);
    }
}
