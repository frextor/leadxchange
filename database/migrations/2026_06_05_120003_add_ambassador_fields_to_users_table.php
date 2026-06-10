<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('ambassador_status', ['none', 'pending', 'approved', 'rejected'])
                  ->default('none')->after('badge_level');
            $table->timestamp('ambassador_requested_at')->nullable()->after('ambassador_status');
            $table->timestamp('ambassador_reviewed_at')->nullable()->after('ambassador_requested_at');
            $table->foreignId('ambassador_reviewed_by')->nullable()->after('ambassador_reviewed_at')
                  ->constrained('users')->nullOnDelete();
            $table->text('ambassador_rejection_reason')->nullable()->after('ambassador_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ambassador_reviewed_by');
            $table->dropColumn([
                'ambassador_status',
                'ambassador_requested_at',
                'ambassador_reviewed_at',
                'ambassador_rejection_reason',
            ]);
        });
    }
};
