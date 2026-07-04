<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('consul_status')->nullable()->after('ambassador_status');
            $table->timestamp('consul_nominated_at')->nullable()->after('consul_status');
            $table->foreignId('consul_nominated_by')->nullable()->after('consul_nominated_at')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['consul_nominated_by']);
            $table->dropColumn(['consul_status', 'consul_nominated_at', 'consul_nominated_by']);
        });
    }
};
