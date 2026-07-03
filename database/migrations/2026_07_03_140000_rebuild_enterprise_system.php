<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0. Allow plans.price to be null (enterprise plans have no fixed price)
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
        });

        // 1. Drop old enterprise_invitations (incompatible schema from June migration)
        Schema::dropIfExists('enterprise_invitations');

        // 2. Create enterprise_licenses (one contract per company)
        Schema::dropIfExists('enterprise_licenses');
        Schema::create('enterprise_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holder_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->unsignedSmallInteger('seats_total')->default(1);
            // Holder counts as 1 seat; tracks active invitations
            $table->unsignedSmallInteger('seats_used')->default(1);
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 3. Create enterprise_invitations (one row per invited member seat)
        Schema::create('enterprise_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('enterprise_licenses')->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users');
            $table->string('email');
            // Linked once the invited user accepts (or account is auto-created)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            $table->string('token', 64)->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['license_id', 'email']);
        });

        // 4. Update the enterprise plan: no fixed price, all permissions unlocked
        $enterprisePlanPermissions = [
            'can_view_member_name'         => true,
            'can_view_member_firstname'    => true,
            'can_view_member_photo'        => true,
            'can_view_member_region'       => true,
            'can_view_member_pitch'        => true,
            'can_view_member_video'        => true,
            'can_view_member_contact'      => true,
            'can_send_invitations'         => true,
            'can_receive_invitations'      => true,
            'can_send_mail'                => true,
            'can_receive_mail'             => true,
            'can_reply_mail'               => true,
            'mail_reply_weekly_limit'      => null,
            'can_send_leads'               => true,
            'can_receive_leads'            => true,
            'max_leads_per_month'          => null,
            'can_join_pole'                => true,
            'can_create_pole'              => true,
            'can_organize_group_events'    => true,
            'can_participate_events'       => true,
            'can_organize_regional_events' => true,
            'can_nominate_consul'          => true,
        ];

        DB::table('plans')->where('name', 'enterprise')->update([
            'label'         => 'Entreprise',
            'price'         => null,
            'is_enterprise' => true,
            'contact_cta'   => 'Nous contacter',
            'permissions'   => json_encode($enterprisePlanPermissions),
            'is_active'     => true,
            'sort_order'    => 99,
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_invitations');
        Schema::dropIfExists('enterprise_licenses');

        DB::table('plans')->where('name', 'enterprise')->update([
            'price'         => 200.00,
            'is_enterprise' => false,
            'contact_cta'   => null,
        ]);
    }
};
