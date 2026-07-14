<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('consul_city_id')->nullable()->after('consul_nominated_by');
            $table->unsignedBigInteger('consul_region_id')->nullable()->after('consul_city_id');
        });

        // Backfill existing consuls with their current city/region
        \DB::table('users')
            ->where('consul_status', 'approved')
            ->update([
                'consul_city_id'   => \DB::raw('city_id'),
                'consul_region_id' => \DB::raw('region_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['consul_city_id', 'consul_region_id']);
        });
    }
};
