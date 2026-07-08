<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the existing hardcoded categories
        DB::table('event_categories')->insert([
            ['key' => 'networking',  'label' => 'Networking',        'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'workshop',    'label' => 'Workshop',          'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'conference',  'label' => 'Conference',        'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'pitch',       'label' => 'Pitch',             'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'after_work',  'label' => 'After-work',        'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'webinar',     'label' => 'Online / Webinar',  'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'community',   'label' => 'Community',         'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
