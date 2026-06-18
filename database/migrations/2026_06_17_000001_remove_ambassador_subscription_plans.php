<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $vipId = DB::table('plans')->where('name', 'vip')->value('id');
        $ambassadorIds = DB::table('plans')
            ->whereIn('name', ['ambassador', 'ambassadeur'])
            ->pluck('id');

        if ($vipId && $ambassadorIds->isNotEmpty()) {
            DB::table('subscriptions')
                ->whereIn('plan_id', $ambassadorIds)
                ->update(['plan_id' => $vipId]);
        }

        DB::table('plans')->whereIn('name', ['ambassador', 'ambassadeur'])->delete();
    }

    public function down(): void
    {
        // Ambassador is a user status, not a subscription plan.
    }
};
