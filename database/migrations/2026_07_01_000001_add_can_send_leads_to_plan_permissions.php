<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $plans = DB::table('plans')
            ->whereIn('name', ['basic', 'premium', 'consul', 'ambassadeur', 'enterprise'])
            ->get(['id', 'name', 'permissions']);

        foreach ($plans as $plan) {
            $perms = json_decode($plan->permissions ?? '{}', true) ?: [];
            $perms['can_send_leads'] = true;
            DB::table('plans')->where('id', $plan->id)->update([
                'permissions' => json_encode($perms),
            ]);
        }
    }

    public function down(): void
    {
        $plans = DB::table('plans')
            ->whereIn('name', ['basic', 'premium', 'consul', 'ambassadeur', 'enterprise'])
            ->get(['id', 'permissions']);

        foreach ($plans as $plan) {
            $perms = json_decode($plan->permissions ?? '{}', true) ?: [];
            unset($perms['can_send_leads']);
            DB::table('plans')->where('id', $plan->id)->update([
                'permissions' => json_encode($perms),
            ]);
        }
    }
};
