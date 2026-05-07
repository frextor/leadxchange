<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('company_id');
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->boolean('newsletter')->default(false)->after('onboarding_completed');
            $table->boolean('notifications')->default(true)->after('newsletter');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn(['city_id', 'newsletter', 'notifications']);
        });
    }
};
