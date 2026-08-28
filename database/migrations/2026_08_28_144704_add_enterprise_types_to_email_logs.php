<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE email_logs MODIFY COLUMN type ENUM(
            'verification','password_reset','notification','marketing','test',
            'enterprise_invitation','enterprise_proposal','enterprise_quote_update',
            'system'
        ) NOT NULL DEFAULT 'notification'");
    }

    public function down(): void
    {
        DB::statement("UPDATE email_logs SET type = 'notification' WHERE type NOT IN ('verification','password_reset','notification','marketing','test')");
        DB::statement("ALTER TABLE email_logs MODIFY COLUMN type ENUM('verification','password_reset','notification','marketing','test') NOT NULL DEFAULT 'notification'");
    }
};
