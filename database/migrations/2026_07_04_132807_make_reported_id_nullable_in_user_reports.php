<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop unique constraint before altering column
        DB::statement('ALTER TABLE user_reports DROP INDEX user_reports_reporter_id_reported_id_reason_unique');
        DB::statement('ALTER TABLE user_reports MODIFY reported_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_reports MODIFY reported_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_reports ADD UNIQUE user_reports_reporter_id_reported_id_reason_unique (reporter_id, reported_id, reason)');
    }
};
