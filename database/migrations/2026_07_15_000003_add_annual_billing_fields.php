<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_annual_price_id')->nullable()->after('stripe_price_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->enum('billing_period', ['free', 'monthly', 'annual'])->default('monthly')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('stripe_annual_price_id');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('billing_period');
        });
    }
};
