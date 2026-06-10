<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('remember_token')->index();
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('billing_period');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('plan_id')->index();
            $table->string('stripe_status')->nullable()->after('status');
            $table->timestamp('current_period_end')->nullable()->after('ends_at');
            $table->boolean('cancel_at_period_end')->default(false)->after('current_period_end');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_subscription_id',
                'stripe_status',
                'current_period_end',
                'cancel_at_period_end',
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['stripe_product_id', 'stripe_price_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_customer_id']);
            $table->dropColumn('stripe_customer_id');
        });
    }
};
