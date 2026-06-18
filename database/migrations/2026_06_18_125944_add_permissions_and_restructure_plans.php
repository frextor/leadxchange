<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('features');
        });

        $basicId = DB::table('plans')->where('name', 'basic')->value('id');
        DB::table('subscriptions')
            ->whereIn('plan_id', DB::table('plans')->whereIn('name', ['vip', 'enterprise'])->pluck('id'))
            ->update(['plan_id' => $basicId]);
        DB::table('plans')->whereIn('name', ['vip', 'enterprise'])->delete();

        $now = now();
        DB::table('plans')->insert([
            ['name'=>'premium','label'=>'Prémium','description'=>'Pour les professionnels actifs.','price'=>29.00,'billing_period'=>'monthly','max_leads'=>null,'max_groups'=>null,'max_users'=>1,'is_active'=>true,'sort_order'=>2,'features'=>json_encode(['Voir le nom complet des membres','Envoyer des invitations','Envoyer des mails illimités','Adhérer à un pôle','Participer aux événements']),'permissions'=>json_encode(self::perms('premium')),'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'consul','label'=>'Consul','description'=>'Pour les leaders locaux.','price'=>59.00,'billing_period'=>'monthly','max_leads'=>null,'max_groups'=>null,'max_users'=>1,'is_active'=>true,'sort_order'=>3,'features'=>json_encode(['Tout ce qu\'inclut Prémium','Créer un pôle','Organiser des événements de groupe']),'permissions'=>json_encode(self::perms('consul')),'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'ambassadeur','label'=>'Ambassadeur','description'=>'Pour les ambassadeurs régionaux.','price'=>99.00,'billing_period'=>'monthly','max_leads'=>null,'max_groups'=>null,'max_users'=>1,'is_active'=>true,'sort_order'=>4,'features'=>json_encode(['Tout ce qu\'inclut Consul','Nommer un consul','Organiser des événements régionaux']),'permissions'=>json_encode(self::perms('ambassadeur')),'created_at'=>$now,'updated_at'=>$now],
        ]);

        DB::table('plans')->where('name', 'basic')->update(['permissions' => json_encode(self::perms('basic'))]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
        DB::table('plans')->whereIn('name', ['premium', 'consul', 'ambassadeur'])->delete();
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
            'can_view_member_name'     => true,
            'mail_reply_weekly_limit'  => null,
            'can_send_invitations'     => true,
            'can_send_mail'            => true,
            'can_join_pole'            => true,
        ]);
        $consul = array_merge($premium, [
            'can_create_pole'           => true,
            'can_organize_group_events' => true,
        ]);
        $ambassadeur = array_merge($consul, [
            'can_nominate_consul'           => true,
            'can_organize_regional_events'  => true,
        ]);
        return match($plan) {
            'premium'     => $premium,
            'consul'      => $consul,
            'ambassadeur' => $ambassadeur,
            default       => $basic,
        };
    }
};
