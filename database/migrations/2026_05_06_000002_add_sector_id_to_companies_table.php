<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('sector_id')->nullable()->after('siret')->constrained('sectors')->nullOnDelete();
        });

        // Migrate existing string values → sector_id
        DB::table('companies')->get()->each(function ($company) {
            $sector = DB::table('sectors')->where('name', $company->sector)->first();
            if ($sector) {
                DB::table('companies')->where('id', $company->id)->update(['sector_id' => $sector->id]);
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('sector');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('siret');
        });

        DB::table('companies')->get()->each(function ($company) {
            $sector = DB::table('sectors')->find($company->sector_id);
            if ($sector) {
                DB::table('companies')->where('id', $company->id)->update(['sector' => $sector->name]);
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};
