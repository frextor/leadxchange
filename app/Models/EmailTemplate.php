<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'name', 'subject', 'body', 'variables', 'is_active'];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // ── Template registry ─────────────────────────────────────────────────────

    public const TEMPLATES = [
        'verification' => [
            'name'            => 'Vérification email',
            'default_subject' => 'Vérifiez votre adresse email — LeadXchange',
            'variables'       => ['name', 'verification_url'],
            'sample'          => ['name' => 'Jean Dupont', 'verification_url' => '#'],
        ],
        'password_reset' => [
            'name'            => 'Réinitialisation mot de passe',
            'default_subject' => 'Réinitialisation de votre mot de passe — LeadXchange',
            'variables'       => ['name', 'reset_url', 'expires_in'],
            'sample'          => ['name' => 'Jean Dupont', 'reset_url' => '#', 'expires_in' => '60'],
        ],
        'system_notification' => [
            'name'            => 'Notification système',
            'default_subject' => '{{title}} — LeadXchange',
            'variables'       => ['name', 'title', 'body', 'action_label', 'action_url'],
            'sample'          => [
                'name'         => 'Jean Dupont',
                'title'        => 'Votre profil a été approuvé',
                'body'         => 'Félicitations ! Votre profil LeadXchange vient d\'être approuvé par notre équipe.',
                'action_label' => 'Accéder à mon profil',
                'action_url'   => '#',
            ],
        ],
        'marketing' => [
            'name'            => 'Email marketing',
            'default_subject' => '{{headline}} — LeadXchange',
            'variables'       => ['name', 'headline', 'body', 'cta_label', 'cta_url'],
            'sample'          => [
                'name'      => 'Jean Dupont',
                'headline'  => 'Découvrez nos nouvelles fonctionnalités',
                'body'      => 'LeadXchange vient de lancer de nouvelles fonctionnalités pour booster votre réseau professionnel.',
                'cta_label' => 'Découvrir maintenant',
                'cta_url'   => '#',
            ],
        ],
        'new_group' => [
            'name'            => 'Nouveau groupe dans votre région',
            'default_subject' => 'Nouveau groupe LeadXchange : {{group_name}}',
            'variables'       => ['name', 'group_name', 'group_description', 'group_url', 'city', 'sector', 'creator_name'],
            'sample'          => [
                'name'              => 'Jean Dupont',
                'group_name'        => 'Entrepreneurs Tech Paris',
                'group_description' => 'Un espace d\'échange pour les entrepreneurs du secteur technologique.',
                'group_url'         => '#',
                'city'              => 'Paris',
                'sector'            => 'Technologie',
                'creator_name'      => 'Marie Martin',
            ],
        ],
        'new_event' => [
            'name'            => 'Nouvel événement dans votre région',
            'default_subject' => 'Événement à venir : {{event_title}}',
            'variables'       => ['name', 'event_title', 'event_description', 'event_url', 'city', 'starts_at', 'event_type', 'creator_name', 'price_label'],
            'sample'          => [
                'name'              => 'Jean Dupont',
                'event_title'       => 'Networking B2B Printemps 2026',
                'event_description' => 'Rencontrez les meilleurs professionnels de votre secteur autour d\'un cocktail.',
                'event_url'         => '#',
                'city'              => 'Lyon',
                'starts_at'         => '15 juillet 2026 à 18h30',
                'event_type'        => 'En présentiel',
                'creator_name'      => 'Sophie Bernard',
                'price_label'       => 'Gratuit',
            ],
        ],
    ];

    // ── Core rendering ────────────────────────────────────────────────────────

    /**
     * Resolve a template from DB, interpolate variables, return [subject, body] or null.
     */
    public static function resolve(string $key, array $vars = []): ?array
    {
        $template = Cache::remember("email_template_{$key}", 3600, fn () =>
            static::where('key', $key)->where('is_active', true)->first()
        );

        if (! $template) {
            return null;
        }

        return [
            'subject' => static::interpolate($template->subject, $vars),
            'body'    => static::interpolate($template->body, $vars),
        ];
    }

    public static function interpolate(string $text, array $vars): string
    {
        foreach ($vars as $var => $value) {
            $text = str_replace(
                ['{{' . $var . '}}', '{{ ' . $var . ' }}'],
                $value,
                $text
            );
        }
        return $text;
    }

    // ── Cache management ──────────────────────────────────────────────────────

    public static function clearCache(string $key): void
    {
        Cache::forget("email_template_{$key}");
    }

    protected static function booted(): void
    {
        static::saved(fn (self $t) => static::clearCache($t->key));
        static::deleted(fn (self $t) => static::clearCache($t->key));
    }
}
