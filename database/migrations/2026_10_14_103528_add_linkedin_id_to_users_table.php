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
        Schema::table('users', function (Blueprint $table) {
            // Identifiant stable LinkedIn (claim "sub" OpenID Connect) — contrairement à
            // l'email, il ne change jamais, même si l'utilisateur modifie son adresse
            // email dans l'app. Sert à retrouver le bon compte à chaque reconnexion.
            $table->string('linkedin_id')->nullable()->unique()->after('password_set_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('linkedin_id');
        });
    }
};
