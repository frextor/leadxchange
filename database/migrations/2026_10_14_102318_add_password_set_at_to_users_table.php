<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Marque si l'utilisateur connaît un mot de passe (false pour un compte créé
     * via LinkedIn, dont le mot de passe est généré aléatoirement à l'inscription
     * et n'est jamais communiqué). Permet de ne pas exiger ce mot de passe pour
     * confirmer des actions sensibles (ex. changement d'email) quand il n'existe
     * aucune valeur correcte que l'utilisateur puisse taper.
     *
     * Rétrocompatibilité : les comptes déjà en base sont considérés comme ayant
     * un mot de passe connu (valeur par défaut = created_at) — hypothèse
     * conservatrice, on ne peut pas distinguer rétroactivement les anciens
     * comptes LinkedIn des comptes classiques. Un utilisateur LinkedIn déjà
     * inscrit doit utiliser « mot de passe oublié » une fois, ce qui met à jour
     * ce champ normalement.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_set_at')->nullable()->after('password');
        });

        DB::table('users')->whereNull('password_set_at')->update(['password_set_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_set_at');
        });
    }
};
