<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'           => 'basic',
                'label'          => 'Basic',
                'description'    => 'Démarrez gratuitement et explorez le réseau LeadXchange.',
                'price'          => 0.00,
                'billing_period' => 'free',
                'max_leads'      => 10,
                'max_groups'     => 3,
                'max_users'      => 1,
                'is_active'      => true,
                'sort_order'     => 1,
                'features'       => [
                    '10 leads par mois',
                    'Profils publics visibles',
                    'Accès à la communauté',
                    'Recherche de membres (basique)',
                    'Rejoindre jusqu\'à 3 groupes',
                    'Support par email',
                ],
            ],
            [
                'name'           => 'vip',
                'label'          => 'VIP',
                'description'    => 'Le plan idéal pour les professionnels actifs qui veulent développer leur réseau.',
                'price'          => 60.00,
                'billing_period' => 'monthly',
                'max_leads'      => null,
                'max_groups'     => null,
                'max_users'      => 1,
                'is_active'      => true,
                'sort_order'     => 2,
                'features'       => [
                    'Leads illimités',
                    'Accès complet aux profils',
                    'Messages d\'introduction IA',
                    'Inbox prioritaire',
                    'Recherche avancée & filtres',
                    'Groupes illimités',
                    'Tableau de bord analytics',
                    'Export contacts (CSV)',
                    'Support email & chat',
                ],
            ],
            [
                'name'           => 'enterprise',
                'label'          => 'Entreprise',
                'description'    => 'Plan entreprise incluant jusqu’à 10 utilisateurs.',
                'price'          => 200.00,
                'billing_period' => 'monthly',
                'max_leads'      => null,
                'max_groups'     => null,
                'max_users'      => 10,
                'is_active'      => true,
                'sort_order'     => 3,
                'features'       => [
                    'Tout ce qu\'inclut VIP',
                    'Jusqu\'à 10 utilisateurs',
                    'Invitations équipe',
                    'Création d\'événements',
                    'Support dédié',
                ],
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
