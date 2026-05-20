<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('plans')->insert([
            [
                'name'           => 'basic',
                'label'          => 'Basic',
                'description'    => 'Plan gratuit avec accès limité. Idéal pour découvrir la plateforme.',
                'price'          => 0.00,
                'billing_period' => 'free',
                'max_leads'      => 0,
                'max_groups'     => 3,
                'features'       => json_encode([
                    'max_connections_per_month' => 3,
                    'view_profile_info'         => false,
                    'send_leads'                => false,
                    'join_groups'               => true,
                    'create_events'             => false,
                    'ambassador_badge'          => false,
                ]),
                'is_active'  => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'           => 'vip',
                'label'          => 'VIP',
                'description'    => 'Accès complet à toutes les fonctionnalités de networking et de génération de leads.',
                'price'          => 60.00,
                'billing_period' => 'monthly',
                'max_leads'      => null,
                'max_groups'     => null,
                'features'       => json_encode([
                    'max_connections_per_month' => null,
                    'view_profile_info'         => true,
                    'send_leads'                => true,
                    'join_groups'               => true,
                    'create_events'             => false,
                    'ambassador_badge'          => false,
                ]),
                'is_active'  => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'           => 'ambassador',
                'label'          => 'Ambassador',
                'description'    => 'Tous les avantages VIP + création d\'événements et privilèges exclusifs. Sur approbation uniquement.',
                'price'          => 60.00,
                'billing_period' => 'monthly',
                'max_leads'      => null,
                'max_groups'     => null,
                'features'       => json_encode([
                    'max_connections_per_month' => null,
                    'view_profile_info'         => true,
                    'send_leads'                => true,
                    'join_groups'               => true,
                    'create_events'             => true,
                    'ambassador_badge'          => true,
                    'requires_approval'         => true,
                ]),
                'is_active'  => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('plans')->whereIn('name', ['basic', 'vip', 'ambassador'])->delete();
    }
};
