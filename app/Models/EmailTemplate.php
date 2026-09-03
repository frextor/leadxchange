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
        'consul_nominated' => [
            'name'            => 'Nomination Consul',
            'default_subject' => 'Vous êtes maintenant Consul — LeadXchange',
            'variables'       => ['name', 'dashboard_url'],
            'sample'          => ['name' => 'Jean Dupont', 'dashboard_url' => '#'],
        ],
        'ambassador_nominated' => [
            'name'            => 'Nomination Ambassadeur (directe)',
            'default_subject' => 'Vous êtes maintenant Ambassadeur — LeadXchange',
            'variables'       => ['name', 'dashboard_url'],
            'sample'          => ['name' => 'Jean Dupont', 'dashboard_url' => '#'],
        ],
        'ambassador_approved' => [
            'name'            => 'Demande Ambassadeur approuvée',
            'default_subject' => 'Demande Ambassadeur approuvée — LeadXchange',
            'variables'       => ['name', 'dashboard_url'],
            'sample'          => ['name' => 'Jean Dupont', 'dashboard_url' => '#'],
        ],
        'ambassador_rejected' => [
            'name'            => 'Demande Ambassadeur refusée',
            'default_subject' => 'Demande Ambassadeur non approuvée — LeadXchange',
            'variables'       => ['name', 'reason', 'profile_url'],
            'sample'          => ['name' => 'Jean Dupont', 'reason' => 'Profil incomplet.', 'profile_url' => '#'],
        ],
        'plan_purchased' => [
            'name'            => 'Confirmation d\'achat de plan',
            'default_subject' => 'Votre plan {{plan_label}} est activé — LeadXchange',
            'variables'       => ['name', 'plan_label', 'dashboard_url'],
            'sample'          => ['name' => 'Jean Dupont', 'plan_label' => 'Premium', 'dashboard_url' => '#'],
        ],
        'plan_changed' => [
            'name'            => 'Changement de plan (admin)',
            'default_subject' => 'Votre plan a été mis à jour — LeadXchange',
            'variables'       => ['name', 'plan_label', 'dashboard_url'],
            'sample'          => ['name' => 'Jean Dupont', 'plan_label' => 'Consul', 'dashboard_url' => '#'],
        ],
        'profile_reminder' => [
            'name'            => 'Rappel de complétion de profil',
            'default_subject' => 'Complétez votre profil LeadXchange',
            'variables'       => ['name', 'completion_pct', 'missing_fields', 'profile_url'],
            'sample'          => [
                'name'           => 'Jean Dupont',
                'completion_pct' => '60',
                'missing_fields' => '<ul style="margin:8px 0;padding-left:20px;"><li style="margin:4px 0;">Photo de profil</li><li style="margin:4px 0;">Secteur d\'activité</li><li style="margin:4px 0;">Biographie</li><li style="margin:4px 0;">Services proposés</li></ul>',
                'profile_url'    => '#',
            ],
        ],
        'referral_invitation' => [
            'name'            => 'Invitation parrainage',
            'default_subject' => '{{referrer_name}} vous invite à rejoindre LeadXchange',
            'variables'       => ['referrer_name', 'register_url'],
            'sample'          => [
                'referrer_name' => 'Jean Dupont',
                'register_url'  => '#',
            ],
        ],

        // ── Pack Entreprise ───────────────────────────────────────────────
        'enterprise_proposal' => [
            'name'            => 'Proposition Pack Entreprise',
            'default_subject' => 'Votre proposition Pack Entreprise — {{company_name}}',
            'variables'       => ['name', 'company_name', 'plan_label', 'seats', 'duration_months', 'price', 'proposal_message', 'proposal_url'],
            'sample'          => [
                'name'             => 'Jean Dupont',
                'company_name'     => 'Acme SAS',
                'plan_label'       => 'Entreprise',
                'seats'            => '10',
                'duration_months'  => '12',
                'price'            => '2 400,00 €',
                'proposal_message' => 'Nous sommes ravis de vous soumettre cette proposition adaptée à vos besoins.',
                'proposal_url'     => '#',
            ],
        ],
        'enterprise_quote_accepted' => [
            'name'            => 'Demande Pack Entreprise acceptée',
            'default_subject' => 'Votre demande Pack Entreprise a été acceptée — LeadXchange',
            'variables'       => ['name', 'company_name', 'dashboard_url'],
            'sample'          => [
                'name'          => 'Jean Dupont',
                'company_name'  => 'Acme SAS',
                'dashboard_url' => '#',
            ],
        ],
        'enterprise_quote_rejected' => [
            'name'            => 'Demande Pack Entreprise non retenue',
            'default_subject' => 'Votre demande Pack Entreprise — LeadXchange',
            'variables'       => ['name', 'company_name', 'admin_notes', 'dashboard_url'],
            'sample'          => [
                'name'          => 'Jean Dupont',
                'company_name'  => 'Acme SAS',
                'admin_notes'   => 'Profil non éligible pour le moment.',
                'dashboard_url' => '#',
            ],
        ],
        'feedback_received' => [
            'name'            => 'Confirmation de feedback',
            'default_subject' => 'Merci pour votre retour — LeadXchange',
            'variables'       => ['name', 'message_excerpt', 'dashboard_url'],
            'sample'          => [
                'name'            => 'Jean Dupont',
                'message_excerpt' => 'J\'adore la fonctionnalité de mise en relation, mais il serait bien d\'avoir...',
                'dashboard_url'   => '#',
            ],
        ],

        // ── Points ────────────────────────────────────────────────────────────
        'buy_points_reminder' => [
            'name'            => 'Rappel achat de points',
            'default_subject' => '{{name}}, votre solde de points est faible — LeadXchange',
            'variables'       => ['name', 'balance', 'points_url'],
            'sample'          => [
                'name'       => 'Jean Dupont',
                'balance'    => '0',
                'points_url' => '#',
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
