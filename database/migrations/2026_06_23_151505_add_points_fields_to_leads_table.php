<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->timestamp('rating_due_at')->nullable()->after('accepted_at');
            $table->timestamp('rating_extended_at')->nullable()->after('rating_due_at');
            $table->unsignedTinyInteger('bonus_points')->nullable()->after('rating_extended_at');
            $table->boolean('expiry_processed')->default(false)->after('bonus_points');
            $table->boolean('sender_points_credited')->default(false)->after('expiry_processed');
        });

        // Seed for existing leads
        DB::statement("UPDATE leads SET
            accepted_at = updated_at,
            rating_due_at = DATE_ADD(updated_at, INTERVAL 15 DAY),
            sender_points_credited = 1
            WHERE status IN ('accepted','converted')");
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['accepted_at','rating_due_at','rating_extended_at',
                                'bonus_points','expiry_processed','sender_points_credited']);
        });
    }
};
