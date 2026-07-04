<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add company_name to enterprise_licenses
        Schema::table('enterprise_licenses', function (Blueprint $table) {
            $table->string('company_name', 100)->nullable()->after('holder_user_id');
        });

        // 2. Make enterprise_invitations.email nullable
        DB::statement('ALTER TABLE enterprise_invitations MODIFY COLUMN email VARCHAR(255) NULL');

        // 3. Drop unique constraint (license_id, email) — nulls break uniqueness for available slots
        try {
            DB::statement('ALTER TABLE enterprise_invitations DROP INDEX enterprise_invitations_license_id_email_unique');
        } catch (\Exception $e) {
            // Already gone or different name — ignore
        }

        // 4. Add unique index only on non-null emails per license (partial index via unique on application side)
        // We'll enforce this in code; no DB-level partial index needed for MySQL 5.x compat.

        // 5. Add 'available' to the status enum (before 'pending')
        DB::statement("ALTER TABLE enterprise_invitations MODIFY COLUMN status ENUM('available','pending','active','revoked') NOT NULL DEFAULT 'available'");

        // 6. Drop FK on invited_by, make column nullable, re-add FK with nullOnDelete
        try {
            DB::statement('ALTER TABLE enterprise_invitations DROP FOREIGN KEY enterprise_invitations_invited_by_foreign');
        } catch (\Exception $e) {
            // Constraint name may differ
        }
        DB::statement('ALTER TABLE enterprise_invitations MODIFY COLUMN invited_by BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE enterprise_invitations ADD CONSTRAINT enterprise_invitations_invited_by_foreign FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        // Reverse invited_by FK
        try {
            DB::statement('ALTER TABLE enterprise_invitations DROP FOREIGN KEY enterprise_invitations_invited_by_foreign');
        } catch (\Exception $e) {}
        DB::statement('ALTER TABLE enterprise_invitations MODIFY COLUMN invited_by BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE enterprise_invitations ADD CONSTRAINT enterprise_invitations_invited_by_foreign FOREIGN KEY (invited_by) REFERENCES users(id)');

        // Restore enum
        DB::statement("ALTER TABLE enterprise_invitations MODIFY COLUMN status ENUM('pending','active','revoked') NOT NULL DEFAULT 'pending'");

        // Restore email NOT NULL (will fail if any null values exist)
        DB::statement('ALTER TABLE enterprise_invitations MODIFY COLUMN email VARCHAR(255) NOT NULL');

        // Restore unique index
        Schema::table('enterprise_invitations', function (Blueprint $table) {
            $table->unique(['license_id', 'email']);
        });

        // Remove company_name
        Schema::table('enterprise_licenses', function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }
};
