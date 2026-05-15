<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clear existing rows — they used the old schema (title/category/budget)
        // and are incompatible with the new CDC fields.
        \DB::table('leads')->truncate();

        Schema::table('leads', function (Blueprint $table) {
            // Drop old generic fields
            $table->dropColumn(['title', 'category', 'budget']);

            // CDC-compliant contact fields
            $table->string('company_name', 150)->after('receiver_id');
            $table->string('contact_name', 100)->after('company_name');
            $table->string('contact_email', 150)->nullable()->after('contact_name');
            $table->string('contact_phone', 30)->nullable()->after('contact_email');
            $table->string('contact_position', 100)->nullable()->after('contact_phone');
            $table->date('deadline')->after('contact_position');
            $table->enum('qualification', ['chaud', 'tiede', 'froid'])->default('tiede')->after('deadline');

            // Track deferred points deduction
            $table->boolean('points_deducted')->default(false)->after('status');
            $table->timestamp('rated_bonus_at')->nullable()->after('points_deducted');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'contact_name', 'contact_email', 'contact_phone',
                'contact_position', 'deadline', 'qualification',
                'points_deducted', 'rated_bonus_at',
            ]);

            $table->string('title', 150)->after('receiver_id');
            $table->text('description')->nullable()->change();
            $table->enum('category', [
                'web_dev', 'design', 'marketing', 'consulting',
                'finance', 'legal', 'hr', 'real_estate', 'other',
            ])->default('other');
            $table->decimal('budget', 10, 2)->nullable();
        });
    }
};
