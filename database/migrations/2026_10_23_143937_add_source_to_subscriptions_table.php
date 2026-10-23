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
        Schema::table('subscriptions', function (Blueprint $table) {
            // null = abonnement réel (Stripe/admin) ; 'trial' ou 'promo_code' = accès Premium
            // temporaire non payé, à révoquer automatiquement à l'expiration de trial_ends_at.
            $table->string('source')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
