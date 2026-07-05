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
        Schema::table('group_invitations', function (Blueprint $table) {
            $table->string('type', 20)->default('invitation')->after('status');
            $table->unsignedBigInteger('invited_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('group_invitations', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->unsignedBigInteger('invited_by')->nullable(false)->change();
        });
    }
};
