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
            if (!Schema::hasColumn('plans', 'max_users')) {
                $table->unsignedInteger('max_users')->default(1)->after('max_groups');
            }
        });

        Schema::create('enterprise_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('accepted_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('token_hash', 64)->unique();
            $table->enum('status', ['pending', 'accepted', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'status']);
            $table->index(['email', 'status']);
        });

        $now = now();

        DB::table('plans')->where('name', 'basic')->update([
            'price' => 0.00,
            'billing_period' => 'free',
            'max_users' => 1,
            'sort_order' => 1,
            'is_active' => true,
            'updated_at' => $now,
        ]);

        DB::table('plans')->where('name', 'vip')->update([
            'price' => 60.00,
            'billing_period' => 'monthly',
            'max_users' => 1,
            'sort_order' => 2,
            'is_active' => true,
            'updated_at' => $now,
        ]);

        DB::table('plans')->where('name', 'ambassador')->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);

        DB::table('plans')->updateOrInsert(
            ['name' => 'enterprise'],
            [
                'label' => 'Entreprise',
                'description' => 'Plan entreprise incluant jusqu’à 10 utilisateurs.',
                'price' => 200.00,
                'billing_period' => 'monthly',
                'max_leads' => null,
                'max_groups' => null,
                'max_users' => 10,
                'features' => json_encode([
                    'max_connections_per_month' => null,
                    'view_profile_info' => true,
                    'send_leads' => true,
                    'join_groups' => true,
                    'create_events' => true,
                    'team_invitations' => true,
                    'dedicated_support' => true,
                ]),
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprise_invitations');

        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'max_users')) {
                $table->dropColumn('max_users');
            }
        });

        DB::table('plans')->where('name', 'enterprise')->delete();
        DB::table('plans')->where('name', 'ambassador')->update(['is_active' => true]);
    }
};
