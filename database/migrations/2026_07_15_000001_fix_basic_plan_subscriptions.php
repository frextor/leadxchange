<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $basicPlan = DB::table('plans')
            ->where('price', 0)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $basicPlan) {
            return;
        }

        // Users who have no subscription at all
        $usersWithoutSub = DB::table('users')
            ->where('role', 'user')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('subscriptions')
                  ->whereColumn('subscriptions.user_id', 'users.id');
            })
            ->pluck('id');

        // Users who only have canceled/non-active subscriptions
        $usersWithInactiveSub = DB::table('users')
            ->where('role', 'user')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('subscriptions')
                  ->whereColumn('subscriptions.user_id', 'users.id')
                  ->where('status', 'active');
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('subscriptions')
                  ->whereColumn('subscriptions.user_id', 'users.id');
            })
            ->pluck('id');

        $now = now();

        foreach ($usersWithoutSub as $userId) {
            DB::table('subscriptions')->insert([
                'user_id'    => $userId,
                'plan_id'    => $basicPlan->id,
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($usersWithInactiveSub as $userId) {
            DB::table('subscriptions')
                ->where('user_id', $userId)
                ->update([
                    'plan_id'    => $basicPlan->id,
                    'status'     => 'active',
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // Not reversible — do not remove subscriptions in rollback
    }
};
