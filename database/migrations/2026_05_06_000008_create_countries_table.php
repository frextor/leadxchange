<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Country name in French
            $table->string('code', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('flag', 8);
            $table->timestamps();
        });

        $now = now();
        $countries = [
            // ── Europe ────────────────────────────────────────────
            ['name' => 'France',                'code' => 'FR', 'flag' => '🇫🇷'],
            ['name' => 'Espagne',               'code' => 'ES', 'flag' => '🇪🇸'],
            ['name' => 'Portugal',              'code' => 'PT', 'flag' => '🇵🇹'],
            ['name' => 'Italie',                'code' => 'IT', 'flag' => '🇮🇹'],
            ['name' => 'Allemagne',             'code' => 'DE', 'flag' => '🇩🇪'],
            ['name' => 'Belgique',              'code' => 'BE', 'flag' => '🇧🇪'],
            ['name' => 'Suisse',                'code' => 'CH', 'flag' => '🇨🇭'],
            ['name' => 'Pays-Bas',              'code' => 'NL', 'flag' => '🇳🇱'],
            ['name' => 'Autriche',              'code' => 'AT', 'flag' => '🇦🇹'],
            ['name' => 'Royaume-Uni',           'code' => 'GB', 'flag' => '🇬🇧'],
            ['name' => 'Irlande',               'code' => 'IE', 'flag' => '🇮🇪'],
            ['name' => 'Suède',                 'code' => 'SE', 'flag' => '🇸🇪'],
            ['name' => 'Norvège',               'code' => 'NO', 'flag' => '🇳🇴'],
            ['name' => 'Danemark',              'code' => 'DK', 'flag' => '🇩🇰'],
            ['name' => 'Finlande',              'code' => 'FI', 'flag' => '🇫🇮'],
            ['name' => 'Islande',               'code' => 'IS', 'flag' => '🇮🇸'],
            ['name' => 'Pologne',               'code' => 'PL', 'flag' => '🇵🇱'],
            ['name' => 'Roumanie',              'code' => 'RO', 'flag' => '🇷🇴'],
            ['name' => 'Hongrie',               'code' => 'HU', 'flag' => '🇭🇺'],
            ['name' => 'Tchéquie',              'code' => 'CZ', 'flag' => '🇨🇿'],
            ['name' => 'Slovaquie',             'code' => 'SK', 'flag' => '🇸🇰'],
            ['name' => 'Slovénie',              'code' => 'SI', 'flag' => '🇸🇮'],
            ['name' => 'Croatie',               'code' => 'HR', 'flag' => '🇭🇷'],
            ['name' => 'Serbie',                'code' => 'RS', 'flag' => '🇷🇸'],
            ['name' => 'Bosnie-Herzégovine',    'code' => 'BA', 'flag' => '🇧🇦'],
            ['name' => 'Macédoine du Nord',     'code' => 'MK', 'flag' => '🇲🇰'],
            ['name' => 'Albanie',               'code' => 'AL', 'flag' => '🇦🇱'],
            ['name' => 'Monténégro',            'code' => 'ME', 'flag' => '🇲🇪'],
            ['name' => 'Kosovo',                'code' => 'XK', 'flag' => '🇽🇰'],
            ['name' => 'Grèce',                 'code' => 'GR', 'flag' => '🇬🇷'],
            ['name' => 'Bulgarie',              'code' => 'BG', 'flag' => '🇧🇬'],
            ['name' => 'Moldavie',              'code' => 'MD', 'flag' => '🇲🇩'],
            ['name' => 'Ukraine',               'code' => 'UA', 'flag' => '🇺🇦'],
            ['name' => 'Biélorussie',           'code' => 'BY', 'flag' => '🇧🇾'],
            ['name' => 'Russie',                'code' => 'RU', 'flag' => '🇷🇺'],
            ['name' => 'Estonie',               'code' => 'EE', 'flag' => '🇪🇪'],
            ['name' => 'Lettonie',              'code' => 'LV', 'flag' => '🇱🇻'],
            ['name' => 'Lituanie',              'code' => 'LT', 'flag' => '🇱🇹'],
            ['name' => 'Luxembourg',            'code' => 'LU', 'flag' => '🇱🇺'],
            ['name' => 'Monaco',                'code' => 'MC', 'flag' => '🇲🇨'],
            ['name' => 'Andorre',               'code' => 'AD', 'flag' => '🇦🇩'],
            ['name' => 'Malte',                 'code' => 'MT', 'flag' => '🇲🇹'],
            ['name' => 'Chypre',                'code' => 'CY', 'flag' => '🇨🇾'],
            // ── Maghreb ────────────────────────────────────────────
            ['name' => 'Maroc',                 'code' => 'MA', 'flag' => '🇲🇦'],
            ['name' => 'Algérie',               'code' => 'DZ', 'flag' => '🇩🇿'],
            ['name' => 'Tunisie',               'code' => 'TN', 'flag' => '🇹🇳'],
            ['name' => 'Libye',                 'code' => 'LY', 'flag' => '🇱🇾'],
            ['name' => 'Mauritanie',            'code' => 'MR', 'flag' => '🇲🇷'],
            // ── Moyen-Orient ───────────────────────────────────────
            ['name' => 'Égypte',                'code' => 'EG', 'flag' => '🇪🇬'],
            ['name' => 'Arabie Saoudite',       'code' => 'SA', 'flag' => '🇸🇦'],
            ['name' => 'Émirats arabes unis',   'code' => 'AE', 'flag' => '🇦🇪'],
            ['name' => 'Qatar',                 'code' => 'QA', 'flag' => '🇶🇦'],
            ['name' => 'Koweït',                'code' => 'KW', 'flag' => '🇰🇼'],
            ['name' => 'Bahreïn',               'code' => 'BH', 'flag' => '🇧🇭'],
            ['name' => 'Oman',                  'code' => 'OM', 'flag' => '🇴🇲'],
            ['name' => 'Yémen',                 'code' => 'YE', 'flag' => '🇾🇪'],
            ['name' => 'Jordanie',              'code' => 'JO', 'flag' => '🇯🇴'],
            ['name' => 'Liban',                 'code' => 'LB', 'flag' => '🇱🇧'],
            ['name' => 'Syrie',                 'code' => 'SY', 'flag' => '🇸🇾'],
            ['name' => 'Irak',                  'code' => 'IQ', 'flag' => '🇮🇶'],
            ['name' => 'Iran',                  'code' => 'IR', 'flag' => '🇮🇷'],
            ['name' => 'Israël',                'code' => 'IL', 'flag' => '🇮🇱'],
            ['name' => 'Palestine',             'code' => 'PS', 'flag' => '🇵🇸'],
            ['name' => 'Turquie',               'code' => 'TR', 'flag' => '🇹🇷'],
            ['name' => 'Afghanistan',           'code' => 'AF', 'flag' => '🇦🇫'],
            // ── Afrique subsaharienne ──────────────────────────────
            ['name' => 'Sénégal',               'code' => 'SN', 'flag' => '🇸🇳'],
            ['name' => "Côte d'Ivoire",         'code' => 'CI', 'flag' => '🇨🇮'],
            ['name' => 'Mali',                  'code' => 'ML', 'flag' => '🇲🇱'],
            ['name' => 'Burkina Faso',          'code' => 'BF', 'flag' => '🇧🇫'],
            ['name' => 'Niger',                 'code' => 'NE', 'flag' => '🇳🇪'],
            ['name' => 'Tchad',                 'code' => 'TD', 'flag' => '🇹🇩'],
            ['name' => 'Guinée',                'code' => 'GN', 'flag' => '🇬🇳'],
            ['name' => 'Guinée-Bissau',         'code' => 'GW', 'flag' => '🇬🇼'],
            ['name' => 'Sierra Leone',          'code' => 'SL', 'flag' => '🇸🇱'],
            ['name' => 'Liberia',               'code' => 'LR', 'flag' => '🇱🇷'],
            ['name' => 'Ghana',                 'code' => 'GH', 'flag' => '🇬🇭'],
            ['name' => 'Togo',                  'code' => 'TG', 'flag' => '🇹🇬'],
            ['name' => 'Bénin',                 'code' => 'BJ', 'flag' => '🇧🇯'],
            ['name' => 'Nigeria',               'code' => 'NG', 'flag' => '🇳🇬'],
            ['name' => 'Cameroun',              'code' => 'CM', 'flag' => '🇨🇲'],
            ['name' => 'Gabon',                 'code' => 'GA', 'flag' => '🇬🇦'],
            ['name' => 'Congo',                 'code' => 'CG', 'flag' => '🇨🇬'],
            ['name' => 'RD Congo',              'code' => 'CD', 'flag' => '🇨🇩'],
            ['name' => 'Angola',                'code' => 'AO', 'flag' => '🇦🇴'],
            ['name' => 'Zambie',                'code' => 'ZM', 'flag' => '🇿🇲'],
            ['name' => 'Zimbabwe',              'code' => 'ZW', 'flag' => '🇿🇼'],
            ['name' => 'Mozambique',            'code' => 'MZ', 'flag' => '🇲🇿'],
            ['name' => 'Madagascar',            'code' => 'MG', 'flag' => '🇲🇬'],
            ['name' => 'Éthiopie',              'code' => 'ET', 'flag' => '🇪🇹'],
            ['name' => 'Érythrée',              'code' => 'ER', 'flag' => '🇪🇷'],
            ['name' => 'Somalie',               'code' => 'SO', 'flag' => '🇸🇴'],
            ['name' => 'Djibouti',              'code' => 'DJ', 'flag' => '🇩🇯'],
            ['name' => 'Kenya',                 'code' => 'KE', 'flag' => '🇰🇪'],
            ['name' => 'Tanzanie',              'code' => 'TZ', 'flag' => '🇹🇿'],
            ['name' => 'Ouganda',               'code' => 'UG', 'flag' => '🇺🇬'],
            ['name' => 'Rwanda',                'code' => 'RW', 'flag' => '🇷🇼'],
            ['name' => 'Burundi',               'code' => 'BI', 'flag' => '🇧🇮'],
            ['name' => 'Malawi',                'code' => 'MW', 'flag' => '🇲🇼'],
            ['name' => 'Afrique du Sud',        'code' => 'ZA', 'flag' => '🇿🇦'],
            ['name' => 'Namibie',               'code' => 'NA', 'flag' => '🇳🇦'],
            ['name' => 'Botswana',              'code' => 'BW', 'flag' => '🇧🇼'],
            ['name' => 'Lesotho',               'code' => 'LS', 'flag' => '🇱🇸'],
            ['name' => 'Eswatini',              'code' => 'SZ', 'flag' => '🇸🇿'],
            ['name' => 'Soudan',                'code' => 'SD', 'flag' => '🇸🇩'],
            ['name' => 'Soudan du Sud',         'code' => 'SS', 'flag' => '🇸🇸'],
            // ── Asie ───────────────────────────────────────────────
            ['name' => 'Chine',                 'code' => 'CN', 'flag' => '🇨🇳'],
            ['name' => 'Japon',                 'code' => 'JP', 'flag' => '🇯🇵'],
            ['name' => 'Corée du Sud',          'code' => 'KR', 'flag' => '🇰🇷'],
            ['name' => 'Corée du Nord',         'code' => 'KP', 'flag' => '🇰🇵'],
            ['name' => 'Inde',                  'code' => 'IN', 'flag' => '🇮🇳'],
            ['name' => 'Pakistan',              'code' => 'PK', 'flag' => '🇵🇰'],
            ['name' => 'Bangladesh',            'code' => 'BD', 'flag' => '🇧🇩'],
            ['name' => 'Sri Lanka',             'code' => 'LK', 'flag' => '🇱🇰'],
            ['name' => 'Népal',                 'code' => 'NP', 'flag' => '🇳🇵'],
            ['name' => 'Birmanie',              'code' => 'MM', 'flag' => '🇲🇲'],
            ['name' => 'Thaïlande',             'code' => 'TH', 'flag' => '🇹🇭'],
            ['name' => 'Vietnam',               'code' => 'VN', 'flag' => '🇻🇳'],
            ['name' => 'Cambodge',              'code' => 'KH', 'flag' => '🇰🇭'],
            ['name' => 'Laos',                  'code' => 'LA', 'flag' => '🇱🇦'],
            ['name' => 'Malaisie',              'code' => 'MY', 'flag' => '🇲🇾'],
            ['name' => 'Singapour',             'code' => 'SG', 'flag' => '🇸🇬'],
            ['name' => 'Indonésie',             'code' => 'ID', 'flag' => '🇮🇩'],
            ['name' => 'Philippines',           'code' => 'PH', 'flag' => '🇵🇭'],
            ['name' => 'Kazakhstan',            'code' => 'KZ', 'flag' => '🇰🇿'],
            ['name' => 'Ouzbékistan',           'code' => 'UZ', 'flag' => '🇺🇿'],
            ['name' => 'Azerbaïdjan',           'code' => 'AZ', 'flag' => '🇦🇿'],
            ['name' => 'Géorgie',               'code' => 'GE', 'flag' => '🇬🇪'],
            ['name' => 'Arménie',               'code' => 'AM', 'flag' => '🇦🇲'],
            // ── Amériques ──────────────────────────────────────────
            ['name' => 'États-Unis',            'code' => 'US', 'flag' => '🇺🇸'],
            ['name' => 'Canada',                'code' => 'CA', 'flag' => '🇨🇦'],
            ['name' => 'Mexique',               'code' => 'MX', 'flag' => '🇲🇽'],
            ['name' => 'Guatemala',             'code' => 'GT', 'flag' => '🇬🇹'],
            ['name' => 'Cuba',                  'code' => 'CU', 'flag' => '🇨🇺'],
            ['name' => 'Haïti',                 'code' => 'HT', 'flag' => '🇭🇹'],
            ['name' => 'République dominicaine','code' => 'DO', 'flag' => '🇩🇴'],
            ['name' => 'Colombie',              'code' => 'CO', 'flag' => '🇨🇴'],
            ['name' => 'Venezuela',             'code' => 'VE', 'flag' => '🇻🇪'],
            ['name' => 'Brésil',                'code' => 'BR', 'flag' => '🇧🇷'],
            ['name' => 'Pérou',                 'code' => 'PE', 'flag' => '🇵🇪'],
            ['name' => 'Équateur',              'code' => 'EC', 'flag' => '🇪🇨'],
            ['name' => 'Bolivie',               'code' => 'BO', 'flag' => '🇧🇴'],
            ['name' => 'Argentine',             'code' => 'AR', 'flag' => '🇦🇷'],
            ['name' => 'Chili',                 'code' => 'CL', 'flag' => '🇨🇱'],
            ['name' => 'Uruguay',               'code' => 'UY', 'flag' => '🇺🇾'],
            ['name' => 'Paraguay',              'code' => 'PY', 'flag' => '🇵🇾'],
            // ── Océanie ────────────────────────────────────────────
            ['name' => 'Australie',             'code' => 'AU', 'flag' => '🇦🇺'],
            ['name' => 'Nouvelle-Zélande',      'code' => 'NZ', 'flag' => '🇳🇿'],
            ['name' => 'Papouasie-Nouvelle-Guinée', 'code' => 'PG', 'flag' => '🇵🇬'],
            ['name' => 'Fidji',                 'code' => 'FJ', 'flag' => '🇫🇯'],
        ];

        DB::table('countries')->insert(
            array_map(fn($c) => array_merge($c, ['created_at' => $now, 'updated_at' => $now]), $countries)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
