<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->where('name', 'premium')->update(['label' => 'VIP']);
    }

    public function down(): void
    {
        DB::table('plans')->where('name', 'premium')->update(['label' => 'Prémium']);
    }
};
