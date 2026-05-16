<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        $userIds   = User::pluck('id')->toArray();
        $sectorIds = DB::table('sectors')->pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->warn('No users found — skipping EventsSeeder.');
            return;
        }

        $events = [
            [
                'title'       => 'B2B Growth Summit 2026',
                'description' => 'Une journée complète dédiée aux stratégies de croissance B2B : acquisition, rétention et expansion. Speakers issus de Scale-ups européennes.',
                'type'        => 'in_person',
                'category'    => 'conference',
                'location'    => 'Palais des Congrès, Paris',
                'starts_at'   => now()->addDays(12)->setTime(9, 0),
                'ends_at'     => now()->addDays(12)->setTime(18, 0),
                'cover_color' => '#1E8F88',
                'price'       => 0,
                'max_attendees' => 500,
                'attendees_count' => 287,
            ],
            [
                'title'       => 'Atelier : Automatiser son Outreach LinkedIn',
                'description' => 'Workshop pratique pour mettre en place des séquences d\'outreach automatisées sur LinkedIn sans se faire bannir. Limite 20 places.',
                'type'        => 'virtual',
                'category'    => 'workshop',
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
                'starts_at'   => now()->addDays(5)->setTime(14, 0),
                'ends_at'     => now()->addDays(5)->setTime(16, 30),
                'cover_color' => '#6366F1',
                'price'       => 49,
                'max_attendees' => 20,
                'attendees_count' => 14,
            ],
            [
                'title'       => 'Pitch Night — Startups MedTech',
                'description' => 'Soirée de pitchs pour les startups MedTech en quête de financement. 6 startups pitchent devant 10 investisseurs. Networking après.',
                'type'        => 'in_person',
                'category'    => 'pitch',
                'location'    => 'Station F, Paris 13e',
                'starts_at'   => now()->addDays(8)->setTime(18, 30),
                'ends_at'     => now()->addDays(8)->setTime(21, 30),
                'cover_color' => '#EF4444',
                'price'       => 0,
                'max_attendees' => 80,
                'attendees_count' => 63,
            ],
            [
                'title'       => 'Webinar : IA Générative pour les PME',
                'description' => 'Comment intégrer ChatGPT, Copilot et Claude dans les processus métier d\'une PME sans compétences techniques. Retours d\'expérience concrets.',
                'type'        => 'virtual',
                'category'    => 'webinar',
                'meeting_link' => 'https://zoom.us/j/99887766554',
                'starts_at'   => now()->addDays(3)->setTime(11, 0),
                'ends_at'     => now()->addDays(3)->setTime(12, 30),
                'cover_color' => '#8B5CF6',
                'price'       => 0,
                'max_attendees' => null,
                'attendees_count' => 412,
            ],
            [
                'title'       => 'After-Work Réseau — Lyon Tech',
                'description' => 'Retrouvons-nous autour d\'un verre pour networker entre professionnels de la tech lyonnaise. Inscription libre, venez nombreux !',
                'type'        => 'in_person',
                'category'    => 'after_work',
                'location'    => 'Le Sucre, Lyon 2e',
                'starts_at'   => now()->addDays(7)->setTime(19, 0),
                'ends_at'     => now()->addDays(7)->setTime(22, 0),
                'cover_color' => '#F59E0B',
                'price'       => 0,
                'max_attendees' => 120,
                'attendees_count' => 78,
            ],
            [
                'title'       => 'Masterclass : Lever des fonds en 2026',
                'description' => 'Un GP de fonds de Venture Capital partage les critères de sélection, les erreurs fatales et les meilleures pratiques pour lever une Seed en 2026.',
                'type'        => 'hybrid',
                'category'    => 'conference',
                'location'    => 'Schoolab, Paris 17e',
                'meeting_link' => 'https://meet.google.com/xyz-abcd-efg',
                'starts_at'   => now()->addDays(18)->setTime(10, 0),
                'ends_at'     => now()->addDays(18)->setTime(13, 0),
                'cover_color' => '#3B82F6',
                'price'       => 0,
                'max_attendees' => 200,
                'attendees_count' => 142,
            ],
            [
                'title'       => 'Workshop : Créer son Personal Branding B2B',
                'description' => 'Session intensive pour définir et mettre en oeuvre sa stratégie de personal branding sur LinkedIn et dans les médias professionnels.',
                'type'        => 'virtual',
                'category'    => 'workshop',
                'meeting_link' => 'https://teams.microsoft.com/l/meetup-join/abc',
                'starts_at'   => now()->addDays(21)->setTime(9, 0),
                'ends_at'     => now()->addDays(21)->setTime(12, 0),
                'cover_color' => '#EC4899',
                'price'       => 79,
                'max_attendees' => 30,
                'attendees_count' => 22,
            ],
            [
                'title'       => 'Forum des Exportateurs Franco-Marocains',
                'description' => 'Rencontre B2B entre entreprises françaises souhaitant développer leur présence au Maroc et acteurs économiques locaux.',
                'type'        => 'in_person',
                'category'    => 'networking',
                'location'    => 'Chambre de Commerce France-Maroc, Paris 8e',
                'starts_at'   => now()->addDays(30)->setTime(9, 30),
                'ends_at'     => now()->addDays(30)->setTime(17, 0),
                'cover_color' => '#10B981',
                'price'       => 0,
                'max_attendees' => 300,
                'attendees_count' => 89,
            ],
            [
                'title'       => 'Webinar : CRM & Pipeline — Choisir la bonne stack',
                'description' => 'Comparatif HubSpot / Salesforce / Pipedrive pour les équipes commerciales de 5 à 50 personnes. Démo live et Q&A.',
                'type'        => 'virtual',
                'category'    => 'webinar',
                'meeting_link' => 'https://zoom.us/j/11223344556',
                'starts_at'   => now()->addDays(2)->setTime(15, 0),
                'ends_at'     => now()->addDays(2)->setTime(16, 0),
                'cover_color' => '#14B8A6',
                'price'       => 0,
                'max_attendees' => null,
                'attendees_count' => 234,
            ],
            [
                'title'       => 'Community Meetup — SaaS Fondateurs',
                'description' => 'Session mensuelle de la communauté des fondateurs SaaS : partage de métriques, blocages du moment et entraide entre pairs.',
                'type'        => 'hybrid',
                'category'    => 'community',
                'location'    => 'Kwerk Opéra, Paris 9e',
                'meeting_link' => 'https://meet.google.com/community-saas',
                'starts_at'   => now()->addDays(14)->setTime(18, 0),
                'ends_at'     => now()->addDays(14)->setTime(20, 0),
                'cover_color' => '#F97316',
                'price'       => 0,
                'max_attendees' => 50,
                'attendees_count' => 38,
            ],
            // Past events for testing the "past" section
            [
                'title'       => 'LeadXchange Launch Party',
                'description' => 'Soirée de lancement de la plateforme LeadXchange — merci à tous ceux qui nous ont fait confiance dès le premier jour.',
                'type'        => 'in_person',
                'category'    => 'networking',
                'location'    => 'Hôtel Molitor, Paris 16e',
                'starts_at'   => now()->subDays(15)->setTime(19, 0),
                'ends_at'     => now()->subDays(15)->setTime(23, 0),
                'cover_color' => '#1E8F88',
                'price'       => 0,
                'max_attendees' => 150,
                'attendees_count' => 148,
            ],
            [
                'title'       => 'Webinar : RGPD & Prospection B2B en 2025',
                'description' => 'Ce qu\'il faut savoir sur le RGPD pour prospecter légalement : bases légales, opt-in, délais de conservation et exemples concrets.',
                'type'        => 'virtual',
                'category'    => 'webinar',
                'meeting_link' => 'https://zoom.us/j/archived',
                'starts_at'   => now()->subDays(30)->setTime(11, 0),
                'ends_at'     => now()->subDays(30)->setTime(12, 0),
                'cover_color' => '#6366F1',
                'price'       => 0,
                'max_attendees' => null,
                'attendees_count' => 856,
            ],
        ];

        foreach ($events as $data) {
            if (Event::where('title', $data['title'])->exists()) {
                $this->command->line("  Skipping existing: {$data['title']}");
                continue;
            }

            $creatorId = $userIds[array_rand($userIds)];
            $sectorId  = !empty($sectorIds) ? $sectorIds[array_rand($sectorIds)] : null;

            $attendeesCount = $data['attendees_count'];
            unset($data['attendees_count']);

            $event = Event::create(array_merge($data, [
                'sector_id'       => $sectorId,
                'created_by'      => $creatorId,
                'is_public'       => true,
                'attendees_count' => 0,
            ]));

            // Attach creator as organizer
            DB::table('event_user')->insertOrIgnore([
                'event_id'      => $event->id,
                'user_id'       => $creatorId,
                'role'          => 'organizer',
                'registered_at' => now()->subDays(rand(5, 30)),
            ]);

            // Attach random attendees to approximate the count
            $otherUsers = array_diff($userIds, [$creatorId]);
            shuffle($otherUsers);
            $attendeeSlots = min(count($otherUsers), max(0, $attendeesCount - 1));
            $picks = array_slice($otherUsers, 0, $attendeeSlots);

            foreach ($picks as $uid) {
                DB::table('event_user')->insertOrIgnore([
                    'event_id'      => $event->id,
                    'user_id'       => $uid,
                    'role'          => 'attendee',
                    'registered_at' => now()->subDays(rand(1, 20)),
                ]);
            }

            $event->update(['attendees_count' => count($picks) + 1]);

            $this->command->line("  Created: {$event->title} ({$event->attendees_count} attendees)");
        }

        $this->command->info('EventsSeeder done.');
    }
}
