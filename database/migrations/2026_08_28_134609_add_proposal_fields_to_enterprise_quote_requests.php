<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enterprise_quote_requests', function (Blueprint $table) {
            // Proposition admin
            $table->unsignedInteger('proposed_seats')->nullable()->after('seats_needed');
            $table->unsignedBigInteger('plan_id')->nullable()->after('proposed_seats');
            $table->decimal('proposed_price', 10, 2)->nullable()->after('plan_id');   // prix total proposé
            $table->unsignedInteger('proposed_duration_months')->nullable()->after('proposed_price'); // durée en mois
            $table->text('proposal_message')->nullable()->after('proposed_duration_months'); // message d'accompagnement

            // Paiement Stripe one-off
            $table->string('stripe_payment_link', 500)->nullable()->after('proposal_message');
            $table->string('stripe_price_id', 100)->nullable()->after('stripe_payment_link');  // price créé à la volée
            $table->string('proposal_token', 64)->nullable()->unique()->after('stripe_price_id'); // token URL client

            $table->timestamp('proposal_sent_at')->nullable()->after('proposal_token');
            $table->timestamp('proposal_accepted_at')->nullable()->after('proposal_sent_at');

            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enterprise_quote_requests', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumns([
                'proposed_seats', 'plan_id', 'proposed_price', 'proposed_duration_months',
                'proposal_message', 'stripe_payment_link', 'stripe_price_id',
                'proposal_token', 'proposal_sent_at', 'proposal_accepted_at',
            ]);
        });
    }
};
