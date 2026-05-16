<?php

namespace Database\Seeders;

use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class InboxItemSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        InboxItem::where('user_id', $user->id)->delete();

        $today = now();
        $yesterday = now()->subDay();
        $twoDaysAgo = now()->subDays(2);
        $threeDaysAgo = now()->subDays(3);
        $fiveDaysAgo = now()->subDays(5);
        $twoWeeksAgo = now()->subWeeks(2);
        $threeWeeksAgo = now()->subWeeks(3);

        $items = [
            // --- Aujourd'hui ---
            [
                'kind'          => 'lead-received',
                'type'          => 'action',
                'read'          => false,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(1),
                'actor_name'    => 'Sophie Martin',
                'actor_title'   => 'Directrice Commerciale',
                'actor_company' => 'Innova SAS',
                'lead_ref'      => 'L-284',
                'title'         => 'Nouveau lead reçu · Climanova SARL',
                'preview'       => 'Sophie Martin vous a transmis un lead chaud pour Climanova SARL.',
                'body'          => "Sophie Martin vous a transmis un lead qualifié **chaud** pour **Climanova SARL**.\n\nContact : Jean-Pierre Moulin, Directeur Technique\nBesoin : Audit énergétique bâtiments industriels\nBudget estimé : 45 000 €\n\nÉchéance : 30 mai 2026. Merci d'accepter ou de refuser ce lead dans les 48h.",
            ],
            [
                'kind'          => 'message',
                'type'          => 'message',
                'read'          => false,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(2),
                'actor_name'    => 'Thomas Renard',
                'actor_title'   => 'CEO',
                'actor_company' => 'Digimedia',
                'lead_ref'      => null,
                'title'         => 'Re : Partenariat stratégique Q3',
                'preview'       => "Bonjour, j'ai bien reçu votre proposition. Je reviens vers vous d'ici vendredi.",
                'body'          => "Bonjour,\n\nJ'ai bien reçu votre proposition concernant le partenariat stratégique Q3. C'est une piste intéressante et je souhaite en discuter avec mon équipe avant de vous donner une réponse définitive.\n\nJe reviens vers vous d'ici vendredi avec nos disponibilités pour un call.\n\nCordialement,\nThomas Renard",
            ],
            [
                'kind'          => 'network',
                'type'          => 'info',
                'read'          => false,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(3),
                'actor_name'    => 'Amira Benali',
                'actor_title'   => 'Head of Marketing',
                'actor_company' => 'GreenTech Solutions',
                'lead_ref'      => null,
                'title'         => 'Amira Benali a consulté votre profil',
                'preview'       => 'Amira Benali (Head of Marketing, GreenTech Solutions) a visité votre profil.',
                'body'          => "**Amira Benali** (Head of Marketing chez GreenTech Solutions) a consulté votre profil LeadXchange.\n\nGreenTech Solutions est active dans le secteur de l'énergie renouvelable avec 120 collaborateurs. C'est peut-être une opportunité de connexion !",
            ],
            [
                'kind'          => 'deadline',
                'type'          => 'alert',
                'read'          => false,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(4),
                'actor_name'    => null,
                'actor_title'   => null,
                'actor_company' => null,
                'lead_ref'      => 'L-271',
                'title'         => 'Échéance dans 3 jours · L-271',
                'preview'       => "Le lead Nextronic SAS expire le 18 mai. Pensez à le noter si vous l'avez traité.",
                'body'          => "Le lead **L-271** (Nextronic SAS) arrive à échéance dans **3 jours**, le 18 mai 2026.\n\nSi vous avez traité ce lead avec succès, pensez à le marquer comme **Converti** et à laisser une notation à l'expéditeur. Cela contribue à la qualité du réseau LeadXchange.",
            ],
            [
                'kind'          => 'message',
                'type'          => 'message',
                'read'          => true,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(5),
                'actor_name'    => 'Lucie Fontaine',
                'actor_title'   => 'Business Developer',
                'actor_company' => 'Axelor',
                'lead_ref'      => null,
                'title'         => 'Disponible pour un call cette semaine ?',
                'preview'       => 'Bonjour ! Je souhaitais savoir si vous étiez disponible pour un échange de 20 min.',
                'body'          => "Bonjour,\n\nJe souhaitais savoir si vous étiez disponible pour un échange de 20 minutes cette semaine ou la semaine prochaine ?\n\nNous avons récemment lancé une nouvelle offre d'accompagnement commercial qui pourrait intéresser votre réseau.\n\nBonne journée,\nLucie Fontaine",
            ],
            [
                'kind'          => 'lead-accepted',
                'type'          => 'info',
                'read'          => true,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(6),
                'actor_name'    => 'Marc Dubois',
                'actor_title'   => 'Directeur Général',
                'actor_company' => 'Optima Conseil',
                'lead_ref'      => 'L-278',
                'title'         => 'Lead L-278 accepté par Marc Dubois',
                'preview'       => 'Marc Dubois a accepté votre lead pour DataCore SARL. +1 point crédité.',
                'body'          => "Bonne nouvelle ! **Marc Dubois** (Directeur Général, Optima Conseil) a **accepté** votre lead **L-278** pour DataCore SARL.\n\n**+1 point** a été crédité sur votre solde.\n\nSi ce lead se concrétise en client, Marc pourra le marquer comme Converti et vous laisser une notation.",
            ],
            [
                'kind'          => 'lead-converted',
                'type'          => 'info',
                'read'          => true,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(8),
                'actor_name'    => 'Isabelle Moreau',
                'actor_title'   => 'Responsable Développement',
                'actor_company' => 'Velox Industries',
                'lead_ref'      => 'L-259',
                'title'         => 'Lead L-259 converti !',
                'preview'       => 'Isabelle Moreau a marqué votre lead comme converti. Excellent travail !',
                'body'          => "Félicitations ! **Isabelle Moreau** a marqué le lead **L-259** (BioPlastics EURL) comme **Converti**.\n\nVotre lead a abouti à une vraie opportunité commerciale. Merci pour la qualité de vos recommandations !\n\n*Pensez à continuer à alimenter votre réseau avec des leads qualifiés.*",
            ],
            [
                'kind'          => 'connection',
                'type'          => 'info',
                'read'          => true,
                'archived'      => false,
                'ts'            => $today->copy()->subHours(9),
                'actor_name'    => 'Karim Aziz',
                'actor_title'   => 'Associé',
                'actor_company' => 'Nexus Partners',
                'lead_ref'      => null,
                'title'         => 'Karim Aziz souhaite se connecter',
                'preview'       => 'Karim Aziz (Associé, Nexus Partners) vous a envoyé une demande de connexion.',
                'body'          => "**Karim Aziz** (Associé chez Nexus Partners) vous a envoyé une demande de connexion.\n\nNexus Partners est un cabinet de conseil en stratégie et M&A basé à Paris. Karim intervient notamment dans les secteurs énergie et industrie.\n\nVous avez 2 connexions en commun.",
            ],

            // --- Cette semaine ---
            [
                'kind'          => 'lead-reminder',
                'type'          => 'alert',
                'read'          => false,
                'archived'      => false,
                'ts'            => $threeDaysAgo->copy()->setHour(9),
                'actor_name'    => null,
                'actor_title'   => null,
                'actor_company' => null,
                'lead_ref'      => 'L-265',
                'title'         => 'Rappel J+15 · Lead L-265 non noté',
                'preview'       => "Vous n'avez pas encore noté le lead Horizon Tech. Votre avis compte !",
                'body'          => "Il y a 15 jours, vous avez reçu le lead **L-265** (Horizon Tech). Il a été accepté mais n'a pas encore été noté.\n\nVotre notation aide à maintenir la qualité du réseau et récompense les bons échangeurs. Prenez 30 secondes pour noter ce lead maintenant.",
            ],
            [
                'kind'          => 'network',
                'type'          => 'info',
                'read'          => true,
                'archived'      => false,
                'ts'            => $fiveDaysAgo->copy()->setHour(14),
                'actor_name'    => 'Clara Petit',
                'actor_title'   => 'Ingénieure Commerciale',
                'actor_company' => 'Sectoria',
                'lead_ref'      => null,
                'title'         => 'Clara Petit a rejoint votre groupe',
                'preview'       => 'Clara Petit a rejoint le groupe "BTP & Rénovation Énergétique".',
                'body'          => "**Clara Petit** (Ingénieure Commerciale, Sectoria) a rejoint le groupe **BTP & Rénovation Énergétique** dont vous êtes membre.\n\nC'est une opportunité d'échange de leads dans le secteur de la rénovation énergétique.",
            ],
            [
                'kind'          => 'lead-rejected',
                'type'          => 'info',
                'read'          => true,
                'archived'      => false,
                'ts'            => $fiveDaysAgo->copy()->setHour(11),
                'actor_name'    => 'Paul Leclerc',
                'actor_title'   => 'Commercial Senior',
                'actor_company' => 'Arko Solutions',
                'lead_ref'      => 'L-269',
                'title'         => 'Lead L-269 refusé par Paul Leclerc',
                'preview'       => "Paul Leclerc n'a pas pu traiter ce lead. Pensez à le retransmettre.",
                'body'          => "**Paul Leclerc** a refusé le lead **L-269** (Metatech SAS). Il n'était pas en mesure de traiter ce prospect actuellement.\n\nSi ce lead est toujours d'actualité, vous pouvez le retransmettre à un autre membre de votre réseau.",
            ],

            // --- Plus ancien ---
            [
                'kind'          => 'lead-received',
                'type'          => 'action',
                'read'          => true,
                'archived'      => false,
                'ts'            => $twoWeeksAgo->copy()->setHour(10),
                'actor_name'    => 'Nadia Rousseau',
                'actor_title'   => 'Business Developer',
                'actor_company' => 'Proxima Group',
                'lead_ref'      => 'L-251',
                'title'         => 'Nouveau lead reçu · Terranext SA',
                'preview'       => 'Nadia Rousseau vous a transmis un lead tiède pour Terranext SA.',
                'body'          => "Nadia Rousseau vous a transmis un lead qualifié **tiède** pour **Terranext SA**.\n\nContact : Marie-Hélène Vidal, Responsable Achats\nBesoin : Externalisation logistique\nBudget estimé : 80 000 €\n\nÉchéance : 20 juin 2026.",
            ],
            [
                'kind'          => 'connection',
                'type'          => 'info',
                'read'          => true,
                'archived'      => true,
                'ts'            => $threeWeeksAgo->copy()->setHour(16),
                'actor_name'    => 'Florian Garnier',
                'actor_title'   => 'Directeur Commercial',
                'actor_company' => 'Vecteur Plus',
                'lead_ref'      => null,
                'title'         => 'Florian Garnier a accepté votre demande',
                'preview'       => 'Vous êtes maintenant connecté avec Florian Garnier.',
                'body'          => "**Florian Garnier** (Directeur Commercial, Vecteur Plus) a accepté votre demande de connexion.\n\nVous faites maintenant partie du même réseau et pouvez vous échanger des leads directement.",
            ],
        ];

        foreach ($items as $data) {
            $data['user_id'] = $user->id;
            InboxItem::create($data);
        }
    }
}
