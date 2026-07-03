<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_enterprise')->default(false)->after('is_active');
            // CTA label shown on the plan card instead of a price button ("Nous contacter", etc.)
            $table->string('contact_cta', 120)->nullable()->after('is_enterprise');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['is_enterprise', 'contact_cta']);
        });
    }
};
