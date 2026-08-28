<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE enterprise_quote_requests MODIFY COLUMN status ENUM('pending','contacted','proposed','converted','closed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Remettre les éventuels 'proposed' en 'contacted' avant de rétrécir l'enum
        DB::statement("UPDATE enterprise_quote_requests SET status = 'contacted' WHERE status = 'proposed'");
        DB::statement("ALTER TABLE enterprise_quote_requests MODIFY COLUMN status ENUM('pending','contacted','converted','closed') NOT NULL DEFAULT 'pending'");
    }
};
