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
            // City locked at ambassador/consul appointment time — independent of profile city changes
            $table->unsignedBigInteger('ambassador_city_id')->nullable()->after('ambassador_reviewed_by');
            $table->unsignedBigInteger('ambassador_region_id')->nullable()->after('ambassador_city_id');
        });

        // Backfill existing ambassadors with their current city/region
        \DB::table('users')
            ->whereNotNull('ambassador_status')
            ->whereIn('ambassador_status', ['approved'])
            ->update([
                'ambassador_city_id'   => \DB::raw('city_id'),
                'ambassador_region_id' => \DB::raw('region_id'),
            ]);

        // Backfill existing consuls with their current city/region (used for region scoping when acting as consul manager)
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ambassador_city_id', 'ambassador_region_id']);
        });
    }
};
