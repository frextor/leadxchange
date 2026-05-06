<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add country_id FK (nullable for migration safety)
        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('name')->constrained('countries')->nullOnDelete();
        });

        // 2. Populate country_id from existing country_code
        DB::table('cities')->get()->each(function ($city) {
            $country = DB::table('countries')->where('code', $city->country_code)->first();
            if ($country) {
                DB::table('cities')->where('id', $city->id)->update(['country_id' => $country->id]);
            }
        });

        // 3. Drop old string columns
        Schema::table('cities', function (Blueprint $table) {
            $table->dropIndex(['country_code']);
            $table->dropIndex(['name']);
            $table->dropColumn(['country', 'country_code']);
        });

        // 4. Re-add name index
        Schema::table('cities', function (Blueprint $table) {
            $table->index('name');
        });

        // 5. Add all French cities (replaces the 10 seeded + adds ~50 more)
        $now = now();
        $fr = DB::table('countries')->where('code', 'FR')->value('id');

        // Remove existing French cities (will re-insert complete list)
        DB::table('cities')->where('country_id', $fr)->delete();

        $frenchCities = [
            'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Montpellier',
            'Strasbourg', 'Bordeaux', 'Lille', 'Rennes', 'Reims', 'Le Havre',
            'Saint-Étienne', 'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Nîmes',
            'Villeurbanne', 'Aix-en-Provence', 'Le Mans', 'Clermont-Ferrand', 'Brest',
            'Tours', 'Amiens', 'Limoges', 'Metz', 'Perpignan', 'Besançon', 'Orléans',
            'Rouen', 'Mulhouse', 'Caen', 'Nancy', 'Avignon', 'Poitiers', 'Annecy',
            'La Rochelle', 'Bayonne', 'Pau', 'Cannes', 'Antibes', 'Dunkerque',
            'Calais', 'Chambéry', 'Lorient', 'Quimper', 'Narbonne', 'Troyes',
            'Valence', 'Montauban', 'Colmar', 'Bourges', 'Mérignac', 'Roubaix',
            'Versailles', 'Saint-Denis', 'Argenteuil', 'Montreuil', 'Nanterre',
            'Vitry-sur-Seine', 'Créteil', 'Boulogne-Billancourt', 'Courbevoie',
            'Toulon', 'Meaux', 'La Seyne-sur-Mer', 'Ajaccio', 'Bastia',
        ];

        DB::table('cities')->insert(
            array_map(fn($name) => [
                'name'       => $name,
                'country_id' => $fr,
                'created_at' => $now,
                'updated_at' => $now,
            ], array_unique($frenchCities))
        );
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('country')->nullable()->after('name');
            $table->string('country_code', 2)->nullable()->after('country');
        });

        DB::table('cities')->join('countries', 'cities.country_id', '=', 'countries.id')
            ->update([
                'cities.country'      => DB::raw('countries.name'),
                'cities.country_code' => DB::raw('countries.code'),
            ]);

        Schema::table('cities', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
