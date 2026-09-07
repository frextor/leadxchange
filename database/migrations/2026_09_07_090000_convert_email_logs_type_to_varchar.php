<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The `type` column was a MySQL ENUM that didn't include every type string
     * actually used across the codebase (e.g. 'new_group', 'feedback_received',
     * 'enterprise_quote_accepted') — inserting those failed with a truncation
     * error. Converting to a plain VARCHAR removes this class of bug entirely,
     * since new email types no longer require a migration to register.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE email_logs MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'notification'");
    }

    public function down(): void
    {
        DB::statement("UPDATE email_logs SET type = 'notification' WHERE type NOT IN (
            'verification','password_reset','notification','marketing','test',
            'enterprise_invitation','enterprise_proposal','enterprise_quote_update','system'
        )");
        DB::statement("ALTER TABLE email_logs MODIFY COLUMN type ENUM(
            'verification','password_reset','notification','marketing','test',
            'enterprise_invitation','enterprise_proposal','enterprise_quote_update',
            'system'
        ) NOT NULL DEFAULT 'notification'");
    }
};
