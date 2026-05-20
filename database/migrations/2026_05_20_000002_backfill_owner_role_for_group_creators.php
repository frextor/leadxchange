<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set role = 'owner' in group_user for every group's creator
        DB::statement("
            UPDATE group_user gu
            INNER JOIN groups g ON g.id = gu.group_id
            SET gu.role = 'owner'
            WHERE g.created_by = gu.user_id
        ");
    }

    public function down(): void
    {
        // Revert creators back to 'member' (best-effort rollback)
        DB::statement("
            UPDATE group_user gu
            INNER JOIN groups g ON g.id = gu.group_id
            SET gu.role = 'member'
            WHERE g.created_by = gu.user_id AND gu.role = 'owner'
        ");
    }
};
