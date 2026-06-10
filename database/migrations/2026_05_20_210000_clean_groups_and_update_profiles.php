<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Same fallback logic as seed_demo_users: prefer ID 52, otherwise first user.
        $mainUserId = DB::table('users')->where('id', 52)->value('id')
            ?? DB::table('users')->min('id');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ── Main user profile: add job title + professional avatar ───────────
        if ($mainUserId) {
            DB::table('profiles')->where('user_id', $mainUserId)->update([
                'job_title'  => 'CEO & Co-Founder',
                'avatar'     => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80',
                'updated_at' => now(),
            ]);
        }

        // ── Delete junk groups (keep 3, 6, 9) ────────────────────────────────
        $deleteGroupIds = [1, 2, 4, 5, 7, 8];

        $postIds = DB::table('group_posts')->whereIn('group_id', $deleteGroupIds)->pluck('id');
        DB::table('group_post_comments')->whereIn('post_id', $postIds)->delete();
        DB::table('group_posts')->whereIn('group_id', $deleteGroupIds)->delete();
        DB::table('group_user')->whereIn('group_id', $deleteGroupIds)->delete();
        DB::table('group_invitations')->whereIn('group_id', $deleteGroupIds)->delete();
        DB::table('groups')->whereIn('id', $deleteGroupIds)->delete();

        // ── Update remaining groups with real names + Unsplash covers ─────────
        DB::table('groups')->where('id', 3)->update([
            'name'        => 'Sales Excellence',
            'description' => 'Une communauté dédiée aux professionnels de la vente B2B. Partagez vos meilleures pratiques, deals et stratégies de closing.',
            'cover_photo' => 'https://images.unsplash.com/photo-1556761175-4b46a572b786?w=800&q=80',
            'cover_color' => '#1E8F88',
            'sector_id'   => 4,
            'updated_at'  => now(),
        ]);

        DB::table('groups')->where('id', 6)->update([
            'name'        => 'Tech & Innovation Hub',
            'description' => 'Le hub des développeurs, CTO et passionnés de tech en Afrique du Nord et EMEA. Startups, SaaS, IA et bien plus.',
            'cover_photo' => 'https://images.unsplash.com/photo-1488590528505-98d2b5aba04b?w=800&q=80',
            'cover_color' => '#6366F1',
            'updated_at'  => now(),
        ]);

        DB::table('groups')->where('id', 9)->update([
            'name'        => 'SaaS Leaders EMEA',
            'description' => 'Réseau des leaders SaaS en Europe, Afrique et Moyen-Orient. Growth, product-led, revenus récurrents et scaling.',
            'cover_photo' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&q=80',
            'cover_color' => '#0D2B45',
            'sector_id'   => 8,
            'updated_at'  => now(),
        ]);

        $now = now();

        // ── Create two more quality groups owned by main user ─────────────────
        $createdBy = $mainUserId ?? 1;

        $g1 = DB::table('groups')->insertGetId([
            'name'          => 'B2B Growth Network',
            'description'   => 'Stratégies de croissance B2B, prospection, ABM et génération de leads qualifiés. Rejoignez les meilleurs growth hackers.',
            'cover_photo'   => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800&q=80',
            'cover_color'   => '#E09010',
            'sector_id'     => 4,
            'is_public'     => true,
            'created_by'    => $createdBy,
            'members_count' => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        DB::table('group_user')->insert(['group_id' => $g1, 'user_id' => $createdBy, 'role' => 'owner']);

        $g2 = DB::table('groups')->insertGetId([
            'name'          => 'Entrepreneurs Maroc',
            'description'   => 'La communauté des entrepreneurs et startuppers marocains. Networking, opportunités, financement et partenariats.',
            'cover_photo'   => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800&q=80',
            'cover_color'   => '#DC2626',
            'sector_id'     => null,
            'is_public'     => true,
            'created_by'    => $createdBy,
            'members_count' => 1,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        DB::table('group_user')->insert(['group_id' => $g2, 'user_id' => $createdBy, 'role' => 'owner']);

        // ── Add demo users as members of each group ───────────────────────────
        $demoUserIds = DB::table('users')->whereIn('email', [
            'antoine.moreau@salesforce.com',
            'nadia.benali@hubspot.com',
            'thomas.keller@zendesk.com',
            'imane.rachidi@oracle.com',
            'lucas.ferreira@twilio.com',
            'camille.dupont@adobe.com',
        ])->pluck('id');

        $memberships = [];
        foreach ($demoUserIds as $uid) {
            if ($uid == $createdBy) continue; // already inserted as owner
            $memberships[] = ['group_id' => 3,   'user_id' => $uid, 'role' => 'member'];
            $memberships[] = ['group_id' => $g1, 'user_id' => $uid, 'role' => 'member'];
        }
        foreach ($demoUserIds->take(3) as $uid) {
            if ($uid == $createdBy) continue;
            $memberships[] = ['group_id' => 6,   'user_id' => $uid, 'role' => 'member'];
            $memberships[] = ['group_id' => 9,   'user_id' => $uid, 'role' => 'member'];
            $memberships[] = ['group_id' => $g2, 'user_id' => $uid, 'role' => 'member'];
        }
        if ($memberships) {
            DB::table('group_user')->insertOrIgnore($memberships);
        }

        // Update member counts
        foreach ([$g1, $g2, 3, 6, 9] as $gid) {
            DB::table('groups')->where('id', $gid)->update([
                'members_count' => DB::table('group_user')->where('group_id', $gid)->count(),
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void {}
};
