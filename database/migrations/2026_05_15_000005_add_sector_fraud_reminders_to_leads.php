<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('sector_id')->nullable()->after('qualification')->constrained()->nullOnDelete();
            $table->boolean('fraud_reported')->default(false)->after('rated_bonus_at');
            $table->string('fraud_reason', 100)->nullable()->after('fraud_reported');
            $table->timestamp('fraud_reported_at')->nullable()->after('fraud_reason');
            $table->timestamp('reminder_15_sent_at')->nullable()->after('fraud_reported_at');
            $table->timestamp('reminder_25_sent_at')->nullable()->after('reminder_15_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sector_id');
            $table->dropColumn([
                'fraud_reported', 'fraud_reason', 'fraud_reported_at',
                'reminder_15_sent_at', 'reminder_25_sent_at',
            ]);
        });
    }
};
