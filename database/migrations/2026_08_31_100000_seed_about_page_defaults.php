<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agrandir la colonne value pour accepter du contenu HTML long
        Schema::table('system_settings', function (Blueprint $table) {
            $table->text('value')->nullable()->change();
        });

        $now = now();

        $defaults = [
            'about_enabled' => [
                'value' => '1',
                'type'  => 'bool',
            ],
            'about_title' => [
                'value' => 'À propos de LeadXchange',
                'type'  => 'string',
            ],
            'about_tagline' => [
                'value' => 'La plateforme professionnelle qui connecte les talents et les entreprises en France',
                'type'  => 'string',
            ],
            'about_mission' => [
                'value' => 'Faciliter les connexions professionnelles en offrant un espace de networking intelligent, humain et efficace pour les professionnels et les entreprises en France.',
                'type'  => 'string',
            ],
            'about_content' => [
                'value' => '<h2>Notre histoire</h2>
<p>LeadXchange a été fondée avec la volonté de transformer la façon dont les professionnels se rencontrent et collaborent. Face à un marché du travail en pleine mutation, nous avons conçu une plateforme qui place la qualité des échanges au cœur de chaque interaction.</p>

<h2>Ce que nous proposons</h2>
<ul>
  <li><strong>Networking ciblé</strong> : Connectez-vous avec des professionnels qui correspondent réellement à vos objectifs.</li>
  <li><strong>Groupes thématiques</strong> : Rejoignez des communautés actives autour de vos secteurs d\'activité et centres d\'intérêt.</li>
  <li><strong>Événements professionnels</strong> : Participez à des rencontres B2B, conférences et forums métiers.</li>
  <li><strong>Pack Entreprise</strong> : Des solutions sur mesure pour les équipes et les recruteurs.</li>
</ul>

<h2>Nos valeurs</h2>
<ul>
  <li><strong>Confiance</strong> : Chaque profil est vérifié. Nous créons un environnement sûr et sérieux.</li>
  <li><strong>Qualité</strong> : Nous privilégions la pertinence des connexions à la quantité.</li>
  <li><strong>Accessibilité</strong> : Une interface simple et intuitive, pensée pour tous les professionnels.</li>
  <li><strong>Innovation</strong> : Nous évoluons en permanence pour répondre aux besoins d\'un marché en mouvement.</li>
</ul>

<h2>Nous contacter</h2>
<p>Une question, une suggestion ou un partenariat ? Notre équipe est à votre écoute. Écrivez-nous à <a href="mailto:contact@leadxchange.com">contact@leadxchange.com</a> — nous répondons sous 48 h ouvrées.</p>',
                'type'  => 'text',
            ],
            'about_contact_email' => [
                'value' => 'contact@leadxchange.com',
                'type'  => 'string',
            ],
            'about_founded_year' => [
                'value' => '2024',
                'type'  => 'string',
            ],
            'about_cta_label' => [
                'value' => 'Rejoindre LeadXchange',
                'type'  => 'string',
            ],
            'about_cta_url' => [
                'value' => '/register',
                'type'  => 'string',
            ],
        ];

        foreach ($defaults as $key => $data) {
            // Only insert if no value exists yet (don't overwrite admin customisations)
            $exists = DB::table('system_settings')
                ->where('key', $key)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->exists();

            if (! $exists) {
                DB::table('system_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value'      => $data['value'],
                        'group'      => 'about_page',
                        'type'       => $data['type'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('group', 'about_page')
            ->delete();
    }
};
