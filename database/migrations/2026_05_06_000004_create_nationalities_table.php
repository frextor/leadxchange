<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Nationality in French: "Marocain(e)"
            $table->string('country');        // Country name in French: "Maroc"
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('flag', 8);        // Emoji flag
            $table->timestamps();
        });

        $now = now();
        DB::table('nationalities')->insert([
            // Maghreb & Moyen-Orient
            ['name' => 'Marocain(e)',       'country' => 'Maroc',               'code' => 'MA', 'flag' => '🇲🇦', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Algérien(ne)',       'country' => 'Algérie',             'code' => 'DZ', 'flag' => '🇩🇿', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tunisien(ne)',       'country' => 'Tunisie',             'code' => 'TN', 'flag' => '🇹🇳', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Libyen(ne)',         'country' => 'Libye',               'code' => 'LY', 'flag' => '🇱🇾', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mauritanien(ne)',    'country' => 'Mauritanie',          'code' => 'MR', 'flag' => '🇲🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Égyptien(ne)',       'country' => 'Égypte',              'code' => 'EG', 'flag' => '🇪🇬', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Saoudien(ne)',       'country' => 'Arabie Saoudite',     'code' => 'SA', 'flag' => '🇸🇦', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Émirati(e)',         'country' => 'Émirats arabes unis', 'code' => 'AE', 'flag' => '🇦🇪', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Qatari(e)',          'country' => 'Qatar',               'code' => 'QA', 'flag' => '🇶🇦', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Koweïtien(ne)',      'country' => 'Koweït',              'code' => 'KW', 'flag' => '🇰🇼', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bahreïni(e)',        'country' => 'Bahreïn',             'code' => 'BH', 'flag' => '🇧🇭', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Omanais(e)',         'country' => 'Oman',                'code' => 'OM', 'flag' => '🇴🇲', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Jordanien(ne)',      'country' => 'Jordanie',            'code' => 'JO', 'flag' => '🇯🇴', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Libanais(e)',        'country' => 'Liban',               'code' => 'LB', 'flag' => '🇱🇧', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Syrien(ne)',         'country' => 'Syrie',               'code' => 'SY', 'flag' => '🇸🇾', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Irakien(ne)',        'country' => 'Irak',                'code' => 'IQ', 'flag' => '🇮🇶', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Iranien(ne)',        'country' => 'Iran',                'code' => 'IR', 'flag' => '🇮🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Turc / Turque',      'country' => 'Turquie',             'code' => 'TR', 'flag' => '🇹🇷', 'created_at' => $now, 'updated_at' => $now],
            // Afrique subsaharienne
            ['name' => 'Sénégalais(e)',      'country' => 'Sénégal',             'code' => 'SN', 'flag' => '🇸🇳', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ivoirien(ne)',        'country' => "Côte d'Ivoire",       'code' => 'CI', 'flag' => '🇨🇮', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Malien(ne)',          'country' => 'Mali',                'code' => 'ML', 'flag' => '🇲🇱', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Burkinabé',           'country' => 'Burkina Faso',        'code' => 'BF', 'flag' => '🇧🇫', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Guinéen(ne)',         'country' => 'Guinée',              'code' => 'GN', 'flag' => '🇬🇳', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Camerounais(e)',      'country' => 'Cameroun',            'code' => 'CM', 'flag' => '🇨🇲', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Congolais(e)',        'country' => 'Congo',               'code' => 'CG', 'flag' => '🇨🇬', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nigérian(e)',         'country' => 'Nigeria',             'code' => 'NG', 'flag' => '🇳🇬', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ghanéen(ne)',         'country' => 'Ghana',               'code' => 'GH', 'flag' => '🇬🇭', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Éthiopien(ne)',       'country' => 'Éthiopie',            'code' => 'ET', 'flag' => '🇪🇹', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kenyan(e)',           'country' => 'Kenya',               'code' => 'KE', 'flag' => '🇰🇪', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sud-Africain(e)',     'country' => 'Afrique du Sud',      'code' => 'ZA', 'flag' => '🇿🇦', 'created_at' => $now, 'updated_at' => $now],
            // Europe
            ['name' => 'Français(e)',         'country' => 'France',              'code' => 'FR', 'flag' => '🇫🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Espagnol(e)',          'country' => 'Espagne',             'code' => 'ES', 'flag' => '🇪🇸', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Portugais(e)',         'country' => 'Portugal',            'code' => 'PT', 'flag' => '🇵🇹', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Italien(ne)',          'country' => 'Italie',              'code' => 'IT', 'flag' => '🇮🇹', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Allemand(e)',          'country' => 'Allemagne',           'code' => 'DE', 'flag' => '🇩🇪', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Belge',               'country' => 'Belgique',            'code' => 'BE', 'flag' => '🇧🇪', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Suisse',              'country' => 'Suisse',              'code' => 'CH', 'flag' => '🇨🇭', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Néerlandais(e)',       'country' => 'Pays-Bas',            'code' => 'NL', 'flag' => '🇳🇱', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Britannique',          'country' => 'Royaume-Uni',         'code' => 'GB', 'flag' => '🇬🇧', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Irlandais(e)',         'country' => 'Irlande',             'code' => 'IE', 'flag' => '🇮🇪', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Suédois(e)',           'country' => 'Suède',               'code' => 'SE', 'flag' => '🇸🇪', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Norvégien(ne)',        'country' => 'Norvège',             'code' => 'NO', 'flag' => '🇳🇴', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Danois(e)',            'country' => 'Danemark',            'code' => 'DK', 'flag' => '🇩🇰', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finnois(e)',           'country' => 'Finlande',            'code' => 'FI', 'flag' => '🇫🇮', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Polonais(e)',          'country' => 'Pologne',             'code' => 'PL', 'flag' => '🇵🇱', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Roumain(e)',           'country' => 'Roumanie',            'code' => 'RO', 'flag' => '🇷🇴', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grec / Grecque',       'country' => 'Grèce',               'code' => 'GR', 'flag' => '🇬🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Russe',               'country' => 'Russie',              'code' => 'RU', 'flag' => '🇷🇺', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ukrainien(ne)',        'country' => 'Ukraine',             'code' => 'UA', 'flag' => '🇺🇦', 'created_at' => $now, 'updated_at' => $now],
            // Amériques
            ['name' => 'Américain(e)',         'country' => 'États-Unis',          'code' => 'US', 'flag' => '🇺🇸', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Canadien(ne)',         'country' => 'Canada',              'code' => 'CA', 'flag' => '🇨🇦', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Brésilien(ne)',        'country' => 'Brésil',              'code' => 'BR', 'flag' => '🇧🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mexicain(e)',          'country' => 'Mexique',             'code' => 'MX', 'flag' => '🇲🇽', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Argentin(e)',          'country' => 'Argentine',           'code' => 'AR', 'flag' => '🇦🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Colombien(ne)',        'country' => 'Colombie',            'code' => 'CO', 'flag' => '🇨🇴', 'created_at' => $now, 'updated_at' => $now],
            // Asie
            ['name' => 'Chinois(e)',           'country' => 'Chine',               'code' => 'CN', 'flag' => '🇨🇳', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Japonais(e)',          'country' => 'Japon',               'code' => 'JP', 'flag' => '🇯🇵', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Coréen(ne)',           'country' => 'Corée du Sud',        'code' => 'KR', 'flag' => '🇰🇷', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Indien(ne)',           'country' => 'Inde',                'code' => 'IN', 'flag' => '🇮🇳', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pakistanais(e)',       'country' => 'Pakistan',            'code' => 'PK', 'flag' => '🇵🇰', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bangladais(e)',        'country' => 'Bangladesh',          'code' => 'BD', 'flag' => '🇧🇩', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Indonésien(ne)',       'country' => 'Indonésie',           'code' => 'ID', 'flag' => '🇮🇩', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Malaisien(ne)',        'country' => 'Malaisie',            'code' => 'MY', 'flag' => '🇲🇾', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Singapourien(ne)',     'country' => 'Singapour',           'code' => 'SG', 'flag' => '🇸🇬', 'created_at' => $now, 'updated_at' => $now],
            // Océanie
            ['name' => 'Australien(ne)',       'country' => 'Australie',           'code' => 'AU', 'flag' => '🇦🇺', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Néo-Zélandais(e)',    'country' => 'Nouvelle-Zélande',    'code' => 'NZ', 'flag' => '🇳🇿', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('nationalities');
    }
};
