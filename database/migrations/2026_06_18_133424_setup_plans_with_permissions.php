<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('plans', 'permissions')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->json('permissions')->nullable()->after('features');
            });
        }

        // Remove all non-basic plans (no subscriptions on them)
        DB::table('plans')->where('name', '!=', 'basic')->delete();

        // Reset auto_increment so new inserts get IDs 2,3,4,5
        DB::statement('ALTER TABLE plans AUTO_INCREMENT = 2');

        $now = now();
        DB::table('plans')->insert([
            [
                'name' => 'premium', 'label' => 'Prémium',
                'description' => 'Pour les professionnels actifs qui veulent développer leur réseau.',
                'price' => 29.00, 'billing_period' => 'monthly',
                'max_leads' => null, 'max_groups' => null, 'max_users' => 1,
                'is_active' => true, 'sort_order' => 2,
                'features' => json_encode(['Voir le nom complet des membres', 'Envoyer des invitations', 'Mails illimités', 'Adhérer à un pôle', 'Participer aux événements']),
                'permissions' => json_encode(self::perms('premium')),
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'consul', 'label' => 'Consul',
                'description' => 'Pour les leaders locaux qui animent leur communauté.',
                'price' => 59.00, 'billing_period' => 'monthly',
                'max_leads' => null, 'max_groups' => null, 'max_users' => 1,
                'is_active' => true, 'sort_order' => 3,
                'features' => json_encode(['Tout Prémium', 'Créer un pôle', 'Organiser des événements de groupe']),
                'permissions' => json_encode(self::perms('consul')),
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'ambassadeur', 'label' => 'Ambassadeur',
                'description' => 'Pour les ambassadeurs régionaux avec pleins pouvoirs.',
                'price' => 99.00, 'billing_period' => 'monthly',
                'max_leads' => null, 'max_groups' => null, 'max_users' => 1,
                'is_active' => true, 'sort_order' => 4,
                'features' => json_encode(['Tout Consul', 'Nommer un consul', 'Organiser des événements régionaux']),
                'permissions' => json_encode(self::perms('ambassadeur')),
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'enterprise', 'label' => 'Entreprise',
                'description' => 'Plan multi-utilisateurs pour les équipes et organisations.',
                'price' => 200.00, 'billing_period' => 'monthly',
                'max_leads' => null, 'max_groups' => null, 'max_users' => 10,
                'is_active' => true, 'sort_order' => 5,
                'features' => json_encode(["Tout Ambassadeur", "Jusqu'à 10 utilisateurs", 'Invitations équipe', 'Support dédié']),
                'permissions' => json_encode(self::perms('enterprise')),
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        DB::table('plans')->where('name', 'basic')->update([
            'sort_order'  => 1,
            'permissions' => json_encode(self::perms('basic')),
        ]);
    }

    public function down(): void
    {
        DB::table('plans')->whereIn('name', ['premium', 'consul', 'ambassadeur', 'enterprise'])->delete();
        if (Schema::hasColumn('plans', 'permissions')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('permissions');
            });
        }
    }

    private static function perms(string $plan): array
    {
        $basic = [
            'can_view_member_name'          => false,
            'can_view_member_firstname'     => true,
            'can_view_member_photo'         => true,
            'can_view_member_region'        => true,
            'can_view_member_pitch'         => true,
            'can_view_member_video'         => true,
            'can_receive_invitations'       => true,
            'can_reply_mail'                => true,
            'mail_reply_weekly_limit'       => 1,
            'can_receive_event_invitations' => true,
            'can_receive_mail'              => true,
            'can_send_invitations'          => false,
            'can_send_mail'                 => false,
            'can_join_pole'                 => false,
            'can_participate_events'        => true,
            'can_create_pole'               => false,
            'can_organize_group_events'     => false,
            'can_nominate_consul'           => false,
            'can_organize_regional_events'  => false,
        ];
        $premium = array_merge($basic, [
            'can_view_member_name'    => true,
            'mail_reply_weekly_limit' => null,
            'can_send_invitations'    => true,
            'can_send_mail'           => true,
            'can_join_pole'           => true,
        ]);
        $consul = array_merge($premium, [
            'can_create_pole'           => true,
            'can_organize_group_events' => true,
        ]);
        $ambassadeur = array_merge($consul, [
            'can_nominate_consul'          => true,
            'can_organize_regional_events' => true,
        ]);
        $enterprise = array_merge($ambassadeur, []);

        return match ($plan) {
            'premium'     => $premium,
            'consul'      => $consul,
            'ambassadeur' => $ambassadeur,
            'enterprise'  => $enterprise,
            default       => $basic,
        };
    }
};
