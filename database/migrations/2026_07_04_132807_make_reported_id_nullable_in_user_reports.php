<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: drop FK + drop unique index + make nullable
        DB::statement('
            ALTER TABLE user_reports
                DROP FOREIGN KEY user_reports_reporter_id_foreign,
                DROP INDEX user_reports_reporter_id_reported_id_reason_unique,
                MODIFY reported_id BIGINT UNSIGNED NULL
        ');
        // Step 2: re-add FK (separate statement to avoid duplicate name error)
        DB::statement('
            ALTER TABLE user_reports
                ADD CONSTRAINT user_reports_reporter_id_foreign
                    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_reports DROP FOREIGN KEY user_reports_reporter_id_foreign');
        DB::statement('ALTER TABLE user_reports MODIFY reported_id BIGINT UNSIGNED NOT NULL');
        DB::statement('
            ALTER TABLE user_reports
                ADD CONSTRAINT user_reports_reporter_id_foreign
                    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
                ADD UNIQUE user_reports_reporter_id_reported_id_reason_unique
                    (reporter_id, reported_id, reason)
        ');
    }
};
