<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    public function run(): void
    {
        $userIds   = User::pluck('id')->toArray();
        $sectorIds = DB::table('sectors')->pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn('No users found — skipping GroupsSeeder.');
            return;
        }

        $groups = [
            [
                'name'        => 'Startups & Innovation France',
                'description' => 'Réseau dédié aux startups françaises et aux entrepreneurs en quête de partenaires, d\'investisseurs et d\'opportunités business.',
                'cover_color' => '#6366F1',
            ],
            [
                'name'        => 'BTP & Construction — Île-de-France',
                'description' => 'Groupe des professionnels du bâtiment, de la construction et des travaux publics en région parisienne.',
                'cover_color' => '#F59E0B',
            ],
            [
                'name'        => 'Tech & SaaS Leaders',
                'description' => 'Dirigeants et décideurs du secteur logiciel et SaaS qui souhaitent échanger sur la croissance, le product-market fit et les go-to-market.',
                'cover_color' => '#1E8F88',
            ],
            [
                'name'        => 'Immobilier Commercial & Investissement',
                'description' => 'Investisseurs, agents et promoteurs immobiliers spécialisés dans les actifs commerciaux, bureaux et entrepôts.',
                'cover_color' => '#10B981',
            ],
            [
                'name'        => 'Marketing Digital & Growth',
                'description' => 'Professionnels du marketing digital, growth hackers et experts SEO/SEA qui partagent stratégies et bonnes pratiques.',
                'cover_color' => '#EC4899',
            ],
            [
                'name'        => 'Finance & Capital-Risque',
                'description' => 'Banquiers, analystes financiers, VC et family offices à la recherche de deal-flow et de co-investissements.',
                'cover_color' => '#3B82F6',
            ],
            [
                'name'        => 'RH & Recrutement — ETI & PME',
                'description' => 'DRH, managers RH et consultants en recrutement des entreprises de taille intermédiaire partagent outils et méthodes.',
                'cover_color' => '#8B5CF6',
            ],
            [
                'name'        => 'Commerce International & Export',
                'description' => 'Entreprises exportatrices et consultants en commerce international explorant de nouveaux marchés et partenariats étrangers.',
                'cover_color' => '#F97316',
            ],
            [
                'name'        => 'Santé & MedTech',
                'description' => 'Professionnels de la santé, industriels de la MedTech et investisseurs qui façonnent l\'avenir du secteur médical.',
                'cover_color' => '#14B8A6',
            ],
            [
                'name'        => 'Réseau des Dirigeants Sud-Ouest',
                'description' => 'Cercle de dirigeants et cadres supérieurs basés en Occitanie et Nouvelle-Aquitaine pour créer des synergies locales.',
                'cover_color' => '#EF4444',
            ],
        ];

        foreach ($groups as $data) {
            $creatorId = $userIds[array_rand($userIds)];
            $sectorId  = $sectorIds[array_rand($sectorIds)];

            $existing = Group::where('name', $data['name'])->first();
            if ($existing) {
                $this->command->line("  Skipping existing group: {$data['name']}");
                continue;
            }

            $group = Group::create([
                'name'         => $data['name'],
                'description'  => $data['description'],
                'cover_color'  => $data['cover_color'],
                'sector_id'    => $sectorId,
                'created_by'   => $creatorId,
                'is_public'    => true,
                'members_count' => 0,
            ]);

            // Add creator as admin
            $members = [$creatorId];
            DB::table('group_user')->insertOrIgnore([
                'group_id'  => $group->id,
                'user_id'   => $creatorId,
                'role'      => 'admin',
                'joined_at' => now()->subDays(rand(10, 90)),
            ]);

            // Add 2–5 random members
            $others = array_diff($userIds, [$creatorId]);
            shuffle($others);
            $picks = array_slice($others, 0, rand(2, min(5, count($others))));

            foreach ($picks as $uid) {
                DB::table('group_user')->insertOrIgnore([
                    'group_id'  => $group->id,
                    'user_id'   => $uid,
                    'role'      => 'member',
                    'joined_at' => now()->subDays(rand(1, 60)),
                ]);
                $members[] = $uid;
            }

            $group->update(['members_count' => count($members)]);
            $this->command->line("  Created: {$group->name} ({$group->members_count} membres)");
        }

        $this->command->info('GroupsSeeder done.');
    }
}
