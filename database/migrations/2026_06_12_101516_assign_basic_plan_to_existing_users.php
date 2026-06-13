<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $basicPlan = Plan::where('name', 'basic')->first();

        if (! $basicPlan) {
            return;
        }

        // All users (role = user) with no subscription
        $userIds = User::where('role', 'user')
            ->whereNotIn('id', Subscription::select('user_id'))
            ->pluck('id');

        foreach ($userIds as $userId) {
            Subscription::create([
                'user_id' => $userId,
                'plan_id' => $basicPlan->id,
                'status'  => 'active',
            ]);
        }
    }

    public function down(): void
    {
        // Not reversible
    }
};
