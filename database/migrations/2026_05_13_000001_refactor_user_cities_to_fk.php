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
            // city_living_id: rename of existing city_id FK
            $table->foreignId('city_living_id')->nullable()->after('city_id')
                  ->constrained('cities')->nullOnDelete();
            // city_birth_id: new FK
            $table->foreignId('city_birth_id')->nullable()->after('city_living_id')
                  ->constrained('cities')->nullOnDelete();
        });

        // Copy city_id → city_living_id
        DB::statement('UPDATE users SET city_living_id = city_id WHERE city_id IS NOT NULL');

        // Best-effort backfill city_birth_id from city_birth text
        DB::statement('
            UPDATE users u
            JOIN cities c ON LOWER(TRIM(c.name)) = LOWER(TRIM(u.city_birth))
            SET u.city_birth_id = c.id
            WHERE u.city_birth IS NOT NULL AND u.city_birth != \'\'
        ');

        // Drop old FK + column city_id
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn(['city_id', 'city_birth', 'city_living']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('city_living')->nullable()->after('city_birth_id');
            $table->string('city_birth')->nullable()->after('city_living');
            $table->foreignId('city_id')->nullable()->after('city_birth')
                  ->constrained('cities')->nullOnDelete();
        });

        DB::statement('UPDATE users SET city_id = city_living_id WHERE city_living_id IS NOT NULL');
        DB::statement('UPDATE users u JOIN cities c ON c.id = u.city_living_id SET u.city_living = c.name WHERE u.city_living_id IS NOT NULL');
        DB::statement('UPDATE users u JOIN cities c ON c.id = u.city_birth_id SET u.city_birth = c.name WHERE u.city_birth_id IS NOT NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_living_id']);
            $table->dropForeign(['city_birth_id']);
            $table->dropColumn(['city_living_id', 'city_birth_id']);
        });
    }
};
