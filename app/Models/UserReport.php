<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    protected $fillable = ['reporter_id','reported_id','reason','details','status','reviewed_by','reviewed_at','admin_note'];
    protected $casts    = ['reviewed_at' => 'datetime'];

    const REASONS = [
        'harcelement'        => 'Harcèlement / Menace / Intimidation',
        'fausses_infos'      => 'Informations fausses ou trompeuses',
        'usurpation'         => 'Usurpation d\'identité',
        'spam'               => 'Spam / Sollicitation non liée à la plateforme',
        'lead_fictif'        => 'Lead fictif ou de mauvaise qualité délibérée',
        'concurrence_deloy'  => 'Pratiques commerciales déloyales',
        'autre'              => 'Autre comportement interdit',
    ];

    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
    public function reported() { return $this->belongsTo(User::class, 'reported_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
