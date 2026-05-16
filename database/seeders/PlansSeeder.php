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
                'name'           => 'ambassadeur',
                'label'          => 'Ambassadeur',
                'description'    => 'Le plan idéal pour les professionnels actifs qui veulent développer leur réseau.',
                'price'          => 29.99,
                'billing_period' => 'monthly',
                'max_leads'      => null,
                'max_groups'     => null,
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
                'name'           => 'premium_gold',
                'label'          => 'Premium Gold',
                'description'    => 'Visibilité maximale et outils exclusifs pour les acteurs clés du réseau.',
                'price'          => 99.99,
                'billing_period' => 'monthly',
                'max_leads'      => null,
                'max_groups'     => null,
                'is_active'      => true,
                'sort_order'     => 3,
                'features'       => [
                    'Tout ce qu\'inclut Ambassadeur',
                    'Badge de profil vérifié',
                    'Mis en avant dans les résultats de recherche',
                    'Co-organisation d\'événements',
                    'Intégrations CRM',
                    'Exports leads en marque blanche',
                    'Account manager dédié',
                    'Support prioritaire 24/7',
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
