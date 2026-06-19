<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ── Permission definitions per plan ──────────────────────────────────────
    private static function perms(string $plan): array
    {
        $basic = [
            // ── Profil membres ───────────────────────────────────────────────
            'can_view_member_name'            => false,   // Voir le nom de famille
            'can_view_member_firstname'       => true,    // Voir le prénom
            'can_view_member_photo'           => true,    // Voir la photo
            'can_view_member_region'          => true,    // Voir la région
            'can_view_member_pitch'           => true,    // Voir le pitch
            'can_view_member_video'           => true,    // Voir la vidéo
            'can_view_member_contact'         => false,   // Voir email/téléphone

            // ── Connexions ────────────────────────────────────────────────────
            'can_send_invitations'            => false,   // Envoyer invitations connexion
            'can_receive_invitations'         => true,    // Recevoir invitations

            // ── Chat / Messages ───────────────────────────────────────────────
            'can_send_mail'                   => false,   // Envoyer des messages
            'can_receive_mail'                => true,    // Recevoir des messages
            'can_reply_mail'                  => true,    // Répondre aux messages
            'mail_reply_weekly_limit'         => 1,       // Limite: 1 réponse/semaine

            // ── Leads ──────────────────────────────────────────────────────────
            'can_send_leads'                  => true,    // Envoyer des leads
            'can_receive_leads'               => true,    // Recevoir des leads
            'max_leads_per_month'             => 10,      // Max leads envoyés/mois
            'can_send_mql'                    => true,    // Envoyer leads MQL
            'can_send_sql'                    => false,   // Envoyer leads SQL
            'can_send_sp'                     => false,   // Envoyer leads SP

            // ── Groupes / Pôles ───────────────────────────────────────────────
            'can_join_pole'                   => false,   // Rejoindre un groupe
            'max_groups_joined'               => 0,       // Max groupes rejoints
            'can_create_pole'                 => false,   // Créer un groupe
            'can_invite_to_group'             => false,   // Inviter dans un groupe
            'can_organize_group_events'       => false,   // Organiser événements de groupe

            // ── Événements ────────────────────────────────────────────────────
            'can_participate_events'          => true,    // Participer aux événements
            'can_receive_event_invitations'   => true,    // Recevoir invitations événements
            'can_create_events'               => false,   // Créer des événements
            'can_organize_regional_events'    => false,   // Organiser événements régionaux

            // ── Spécial ───────────────────────────────────────────────────────
            'can_nominate_consul'             => false,   // Nommer un consul
            'can_add_member'                  => false,   // Ajouter membre (Enterprise)
        ];

        $premium = array_merge($basic, [
            'can_view_member_name'            => true,
            'can_view_member_contact'         => true,
            'can_send_invitations'            => true,
            'can_send_mail'                   => true,
            'mail_reply_weekly_limit'         => null,   // illimité
            'max_leads_per_month'             => null,   // illimité
            'can_send_sql'                    => true,
            'can_send_sp'                     => false,
            'can_join_pole'                   => true,
            'max_groups_joined'               => null,   // illimité
            'can_invite_to_group'             => true,
            'can_create_events'               => true,
        ]);

        $consul = array_merge($premium, [
            'can_send_sp'                     => true,
            'can_create_pole'                 => true,
            'can_organize_group_events'       => true,
        ]);

        $ambassadeur = array_merge($consul, [
            'can_nominate_consul'             => true,
            'can_organize_regional_events'    => true,
        ]);

        $enterprise = array_merge($ambassadeur, [
            'can_add_member'                  => true,
        ]);

        return match ($plan) {
            'premium'     => $premium,
            'consul'      => $consul,
            'ambassadeur' => $ambassadeur,
            'enterprise'  => $enterprise,
            default       => $basic,
        };
    }

    public function up(): void
    {
        $plans = DB::table('plans')->get(['id', 'name']);
        foreach ($plans as $plan) {
            DB::table('plans')->where('id', $plan->id)->update([
                'permissions' => json_encode(self::perms($plan->name)),
            ]);
        }
    }

    public function down(): void
    {
        // No rollback needed — just a data update
    }
};
