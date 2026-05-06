<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // French label
            $table->string('native_name');    // Native label
            $table->string('code', 8)->unique(); // ISO 639-1 / custom
            $table->timestamps();
        });

        $now = now();
        DB::table('languages')->insert([
            ['name' => 'Arabe',             'native_name' => 'العربية',        'code' => 'ar',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Darija',            'native_name' => 'الدارجة',        'code' => 'ary', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Amazigh',           'native_name' => 'ⵜⴰⵎⴰⵣⵉⵖⵜ',      'code' => 'ber', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Français',          'native_name' => 'Français',       'code' => 'fr',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Anglais',           'native_name' => 'English',        'code' => 'en',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Espagnol',          'native_name' => 'Español',        'code' => 'es',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Portugais',         'native_name' => 'Português',      'code' => 'pt',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Allemand',          'native_name' => 'Deutsch',        'code' => 'de',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Italien',           'native_name' => 'Italiano',       'code' => 'it',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Néerlandais',       'native_name' => 'Nederlands',     'code' => 'nl',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Russe',             'native_name' => 'Русский',        'code' => 'ru',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Chinois mandarin',  'native_name' => '普通话',          'code' => 'zh',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Japonais',          'native_name' => '日本語',          'code' => 'ja',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Coréen',            'native_name' => '한국어',          'code' => 'ko',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hindi',             'native_name' => 'हिन्दी',         'code' => 'hi',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Turc',              'native_name' => 'Türkçe',         'code' => 'tr',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Persan',            'native_name' => 'فارسی',          'code' => 'fa',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hébreu',            'native_name' => 'עברית',          'code' => 'he',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bengali',           'native_name' => 'বাংলা',          'code' => 'bn',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Swahili',           'native_name' => 'Kiswahili',      'code' => 'sw',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Haoussa',           'native_name' => 'Hausa',          'code' => 'ha',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Polonais',          'native_name' => 'Polski',         'code' => 'pl',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ukrainien',         'native_name' => 'Українська',     'code' => 'uk',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Roumain',           'native_name' => 'Română',         'code' => 'ro',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Grec',              'native_name' => 'Ελληνικά',       'code' => 'el',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Suédois',           'native_name' => 'Svenska',        'code' => 'sv',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Norvégien',         'native_name' => 'Norsk',          'code' => 'no',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Danois',            'native_name' => 'Dansk',          'code' => 'da',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finnois',           'native_name' => 'Suomi',          'code' => 'fi',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tchèque',           'native_name' => 'Čeština',        'code' => 'cs',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Hongrois',          'native_name' => 'Magyar',         'code' => 'hu',  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
