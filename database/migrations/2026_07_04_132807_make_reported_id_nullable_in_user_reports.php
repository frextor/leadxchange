<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop both FKs + unique index + modify column in one statement so MySQL
        // doesn't complain about the index being needed mid-operation.
        DB::statement('
            ALTER TABLE user_reports
                DROP FOREIGN KEY user_reports_reporter_id_foreign,
                DROP FOREIGN KEY user_reports_reported_id_foreign,
                DROP INDEX user_reports_reporter_id_reported_id_reason_unique,
                MODIFY reported_id BIGINT UNSIGNED NULL
        ');

        // Re-add FKs: reporter keeps CASCADE, reported uses SET NULL (nullable now)
        DB::statement('
            ALTER TABLE user_reports
                ADD CONSTRAINT user_reports_reporter_id_foreign
                    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
                ADD CONSTRAINT user_reports_reported_id_foreign
                    FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE SET NULL
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_reports DROP FOREIGN KEY user_reports_reporter_id_foreign');
        DB::statement('ALTER TABLE user_reports DROP FOREIGN KEY user_reports_reported_id_foreign');
        DB::statement('ALTER TABLE user_reports MODIFY reported_id BIGINT UNSIGNED NOT NULL');
        DB::statement('
            ALTER TABLE user_reports
                ADD CONSTRAINT user_reports_reporter_id_foreign
                    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
                ADD CONSTRAINT user_reports_reported_id_foreign
                    FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE CASCADE,
                ADD UNIQUE user_reports_reporter_id_reported_id_reason_unique
                    (reporter_id, reported_id, reason)
        ');
    }
};
