<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK (it requires the unique index, so must go first)
        DB::statement('ALTER TABLE user_reports DROP FOREIGN KEY user_reports_reported_id_foreign');
        // 2. Drop unique index
        DB::statement('ALTER TABLE user_reports DROP INDEX user_reports_reporter_id_reported_id_reason_unique');
        // 3. Make column nullable
        DB::statement('ALTER TABLE user_reports MODIFY reported_id BIGINT UNSIGNED NULL');
        // 4. Re-add FK with SET NULL (reported user deleted → keep report, lose ref)
        DB::statement('ALTER TABLE user_reports ADD CONSTRAINT user_reports_reported_id_foreign FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_reports DROP FOREIGN KEY user_reports_reported_id_foreign');
        DB::statement('ALTER TABLE user_reports MODIFY reported_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_reports ADD CONSTRAINT user_reports_reported_id_foreign FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_reports ADD UNIQUE user_reports_reporter_id_reported_id_reason_unique (reporter_id, reported_id, reason)');
    }
};
