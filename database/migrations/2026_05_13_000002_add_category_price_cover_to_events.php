<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('category', [
                'networking', 'workshop', 'conference',
                'pitch', 'after_work', 'webinar', 'community',
            ])->nullable()->after('type');
            $table->decimal('price', 8, 2)->nullable()->after('cover_color');
            $table->string('cover_image')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['category', 'price', 'cover_image']);
        });
    }
};
