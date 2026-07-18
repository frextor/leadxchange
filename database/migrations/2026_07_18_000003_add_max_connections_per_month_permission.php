<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Move max_connections_per_month from features → permissions so it is
     * editable from the admin's plan-permissions editor.
     *
     * Values:
     *   basic        → 1  (1 invitation/month)
     *   premium      → null (unlimited)
     *   consul       → null
     *   ambassadeur  → null
     *   enterprise   → null
     */
    public function up(): void
    {
        // 1. Register the permission definition so admin can see & edit it
        DB::table('permission_definitions')->updateOrInsert(
            ['key' => 'max_connections_per_month'],
            [
                'key'        => 'max_connections_per_month',
                'label'      => 'Invitations connexion / mois',
                'category'   => 'Connexions',
                'type'       => 'number',
                'null_label' => 'Illimité',
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Set the permission value per plan
        $limits = [
            'basic'       => 1,
            'premium'     => null,
            'consul'      => null,
            'ambassadeur' => null,
            'enterprise'  => null,
        ];

        $plans = DB::table('plans')->get(['id', 'name', 'permissions']);
        foreach ($plans as $plan) {
            $perms = json_decode($plan->permissions ?? '{}', true) ?: [];
            $limit = array_key_exists($plan->name, $limits) ? $limits[$plan->name] : null;
            $perms['max_connections_per_month'] = $limit;
            DB::table('plans')->where('id', $plan->id)->update([
                'permissions' => json_encode($perms),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permission_definitions')
            ->where('key', 'max_connections_per_month')
            ->delete();

        $plans = DB::table('plans')->get(['id', 'permissions']);
        foreach ($plans as $plan) {
            $perms = json_decode($plan->permissions ?? '{}', true) ?: [];
            unset($perms['max_connections_per_month']);
            DB::table('plans')->where('id', $plan->id)->update([
                'permissions' => json_encode($perms),
            ]);
        }
    }
};
