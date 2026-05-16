<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('label')->after('name');
            $table->text('description')->nullable()->after('label');
            $table->enum('billing_period', ['monthly', 'annual', 'free'])->default('monthly')->after('price');
            $table->unsignedInteger('max_leads')->nullable()->after('billing_period')->comment('null = unlimited');
            $table->unsignedInteger('max_groups')->nullable()->after('max_leads')->comment('null = unlimited');
            $table->boolean('is_active')->default(true)->after('features');
            $table->unsignedTinyInteger('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['label', 'description', 'billing_period', 'max_leads', 'max_groups', 'is_active', 'sort_order']);
        });
    }
};
