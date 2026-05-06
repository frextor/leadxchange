<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');        // Country name in French
            $table->string('country_code', 2); // ISO 3166-1 alpha-2
            $table->timestamps();

            $table->index(['country_code']);
            $table->index(['name']);
        });

        $now = now();

        $cities = [
            // Maroc
            ['name' => 'Casablanca',       'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Rabat',            'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Marrakech',        'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Fès',              'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Tanger',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Agadir',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Meknès',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Oujda',            'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Kénitra',          'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Tétouan',          'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Salé',             'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Mohammedia',       'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'El Jadida',        'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Safi',             'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Béni Mellal',      'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Khouribga',        'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Nador',            'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Settat',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Berrechid',        'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Taza',             'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Larache',          'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Tiznit',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Al Hoceïma',       'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Guelmim',          'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Dakhla',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Laâyoune',         'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Essaouira',        'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Ouarzazate',       'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Errachidia',       'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Ifrane',           'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Khémisset',        'country' => 'Maroc', 'country_code' => 'MA'],
            ['name' => 'Chefchaouen',      'country' => 'Maroc', 'country_code' => 'MA'],
            // Algérie
            ['name' => 'Alger',            'country' => 'Algérie', 'country_code' => 'DZ'],
            ['name' => 'Oran',             'country' => 'Algérie', 'country_code' => 'DZ'],
            ['name' => 'Constantine',      'country' => 'Algérie', 'country_code' => 'DZ'],
            ['name' => 'Annaba',           'country' => 'Algérie', 'country_code' => 'DZ'],
            ['name' => 'Tlemcen',          'country' => 'Algérie', 'country_code' => 'DZ'],
            // Tunisie
            ['name' => 'Tunis',            'country' => 'Tunisie', 'country_code' => 'TN'],
            ['name' => 'Sfax',             'country' => 'Tunisie', 'country_code' => 'TN'],
            ['name' => 'Sousse',           'country' => 'Tunisie', 'country_code' => 'TN'],
            // France
            ['name' => 'Paris',            'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Lyon',             'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Marseille',        'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Toulouse',         'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Nice',             'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Bordeaux',         'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Lille',            'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Strasbourg',       'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Montpellier',      'country' => 'France', 'country_code' => 'FR'],
            ['name' => 'Nantes',           'country' => 'France', 'country_code' => 'FR'],
            // Belgique
            ['name' => 'Bruxelles',        'country' => 'Belgique', 'country_code' => 'BE'],
            ['name' => 'Anvers',           'country' => 'Belgique', 'country_code' => 'BE'],
            // Pays-Bas
            ['name' => 'Amsterdam',        'country' => 'Pays-Bas', 'country_code' => 'NL'],
            ['name' => 'Rotterdam',        'country' => 'Pays-Bas', 'country_code' => 'NL'],
            // Espagne
            ['name' => 'Madrid',           'country' => 'Espagne', 'country_code' => 'ES'],
            ['name' => 'Barcelone',        'country' => 'Espagne', 'country_code' => 'ES'],
            ['name' => 'Séville',          'country' => 'Espagne', 'country_code' => 'ES'],
            // Allemagne
            ['name' => 'Berlin',           'country' => 'Allemagne', 'country_code' => 'DE'],
            ['name' => 'Munich',           'country' => 'Allemagne', 'country_code' => 'DE'],
            ['name' => 'Francfort',        'country' => 'Allemagne', 'country_code' => 'DE'],
            ['name' => 'Hambourg',         'country' => 'Allemagne', 'country_code' => 'DE'],
            // Royaume-Uni
            ['name' => 'Londres',          'country' => 'Royaume-Uni', 'country_code' => 'GB'],
            ['name' => 'Manchester',       'country' => 'Royaume-Uni', 'country_code' => 'GB'],
            ['name' => 'Birmingham',       'country' => 'Royaume-Uni', 'country_code' => 'GB'],
            // Suisse
            ['name' => 'Zurich',           'country' => 'Suisse', 'country_code' => 'CH'],
            ['name' => 'Genève',           'country' => 'Suisse', 'country_code' => 'CH'],
            // Canada
            ['name' => 'Montréal',         'country' => 'Canada', 'country_code' => 'CA'],
            ['name' => 'Toronto',          'country' => 'Canada', 'country_code' => 'CA'],
            ['name' => 'Vancouver',        'country' => 'Canada', 'country_code' => 'CA'],
            // États-Unis
            ['name' => 'New York',         'country' => 'États-Unis', 'country_code' => 'US'],
            ['name' => 'Los Angeles',      'country' => 'États-Unis', 'country_code' => 'US'],
            ['name' => 'Miami',            'country' => 'États-Unis', 'country_code' => 'US'],
            ['name' => 'Chicago',          'country' => 'États-Unis', 'country_code' => 'US'],
            // Moyen-Orient
            ['name' => 'Dubaï',            'country' => 'Émirats arabes unis', 'country_code' => 'AE'],
            ['name' => 'Abu Dhabi',        'country' => 'Émirats arabes unis', 'country_code' => 'AE'],
            ['name' => 'Riyad',            'country' => 'Arabie Saoudite',     'country_code' => 'SA'],
            ['name' => 'Djeddah',          'country' => 'Arabie Saoudite',     'country_code' => 'SA'],
            ['name' => 'Doha',             'country' => 'Qatar',               'country_code' => 'QA'],
            ['name' => 'Koweït City',      'country' => 'Koweït',              'country_code' => 'KW'],
            ['name' => 'Beyrouth',         'country' => 'Liban',               'country_code' => 'LB'],
            ['name' => 'Istanbul',         'country' => 'Turquie',             'country_code' => 'TR'],
            ['name' => 'Ankara',           'country' => 'Turquie',             'country_code' => 'TR'],
            ['name' => 'Le Caire',         'country' => 'Égypte',              'country_code' => 'EG'],
            ['name' => 'Alexandrie',       'country' => 'Égypte',              'country_code' => 'EG'],
            // Afrique subsaharienne
            ['name' => 'Dakar',            'country' => 'Sénégal',             'country_code' => 'SN'],
            ['name' => 'Abidjan',          'country' => "Côte d'Ivoire",       'country_code' => 'CI'],
            ['name' => 'Lagos',            'country' => 'Nigeria',             'country_code' => 'NG'],
            ['name' => 'Nairobi',          'country' => 'Kenya',               'country_code' => 'KE'],
            ['name' => 'Johannesburg',     'country' => 'Afrique du Sud',      'country_code' => 'ZA'],
            ['name' => 'Le Cap',           'country' => 'Afrique du Sud',      'country_code' => 'ZA'],
        ];

        DB::table('cities')->insert(
            array_map(fn($c) => array_merge($c, ['created_at' => $now, 'updated_at' => $now]), $cities)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
