<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('market_addressed_id')->nullable()->constrained('markets')->nullOnDelete()->after('services_offered');
            $table->foreignId('market_target_id')->nullable()->constrained('markets')->nullOnDelete()->after('market_addressed_id');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['market_addressed_id']);
            $table->dropForeign(['market_target_id']);
            $table->dropColumn(['market_addressed_id', 'market_target_id']);
        });
    }
};
