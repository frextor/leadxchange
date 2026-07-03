<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cgu_version')->nullable()->after('newsletter');
            $table->timestamp('cgu_accepted_at')->nullable()->after('cgu_version');
        });

        // Marquer les utilisateurs existants avec la version actuelle
        DB::table('users')->whereNull('cgu_version')
            ->update(['cgu_version' => '1.1', 'cgu_accepted_at' => now()]);

        // Stocker la version courante dans system_settings
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'cgu_current_version'],
            ['value' => '1.1', 'type' => 'string', 'group' => 'legal',
             'description' => 'Version actuelle des CGU', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'cgu_updated_at'],
            ['value' => '2026-06-12', 'type' => 'string', 'group' => 'legal',
             'description' => 'Date de mise à jour des CGU', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cgu_version', 'cgu_accepted_at']);
        });
    }
};
