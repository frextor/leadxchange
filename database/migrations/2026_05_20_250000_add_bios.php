<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $bios = [
            // Extra 10 users
            'sofia.martinez@microsoft.com'  => "Enterprise Account Manager chez Microsoft avec 8 ans d'expérience en vente B2B.\nSpécialisée dans les solutions cloud et la gestion de grands comptes en Europe.",
            'karim.idrissi@sap.com'         => "Sales Director MENA chez SAP, passionné par la transformation digitale des entreprises africaines.\nExpert en ERP, CRM et développement commercial sur les marchés émergents.",
            'julie.bernard@linkedin.com'    => "Talent Acquisition Lead chez LinkedIn, je connecte les meilleurs profils tech et sales avec les bonnes opportunités.\nPassionnée par l'expérience candidat et le recrutement data-driven.",
            'omar.elfassi@stripe.com'       => "Partnerships Manager chez Stripe, je développe l'écosystème fintech en Europe et au Moyen-Orient.\nEx-entrepreneur, convaincu que la tech peut transformer les échanges commerciaux.",
            'lena.schneider@notion.so'      => "Product Marketing Manager chez Notion, je travaille à l'intersection du produit et du marché.\nPassionnée par le SaaS, le PLG et les communautés d'utilisateurs.",
            'mehdi.tazi@hubspot.ma'         => "Growth Hacker spécialisé en inbound marketing et automation pour les startups B2B au Maroc et en Afrique.\nFondateur de plusieurs side-projects SaaS, toujours à la recherche de nouvelles leviers de croissance.",
            'claire.fontaine@salesforce.fr' => "Customer Success Manager chez Salesforce, j'aide les équipes commerciales à maximiser leur ROI CRM.\nAncienne commerciale terrain reconvertie dans le succès client et l'adoption produit.",
            'youssef.amrani@oracle.ma'      => "Pre-Sales Engineer chez Oracle, j'accompagne les prospects dans l'évaluation de solutions cloud et data.\nPassionné par l'architecture technique et la démonstration de valeur métier.",
            'amelia.clarke@shopify.com'     => "Revenue Operations Lead chez Shopify, j'aligne les équipes sales, marketing et CS autour de la donnée.\nExperte en CRM, forecasting et optimisation du cycle de vente.",
            'amine.benkirane@google.com'    => "Strategic Partnerships chez Google, je développe des alliances avec les acteurs tech et media en EMEA.\nEntrepreneur dans l'âme, mentor de startups et speaker dans les conférences tech.",

            // 6 connected demo users
            'antoine.moreau@salesforce.com' => "Sales Engineer chez Salesforce, j'aide les équipes commerciales à déployer des solutions CRM performantes.\nSpécialisé dans les cycles de vente complexes et l'intégration Salesforce en environnement enterprise.",
            'nadia.benali@hubspot.com'       => "Account Executive chez HubSpot, je guide les PME dans leur adoption de l'inbound marketing et du CRM.\nPassionnée par les ventes consultatives et l'alignement marketing-sales.",
            'thomas.keller@zendesk.com'      => "VP Sales EMEA chez Zendesk, je dirige une équipe de 40 commerciaux sur 12 pays.\nEx-fondateur, j'ai vendu ma startup en 2019 et rejoint Zendesk pour scaler les ventes enterprise.",
            'imane.rachidi@oracle.com'       => "Business Developer chez Oracle, spécialisée dans les solutions cloud pour les secteurs finance et industrie.\nFocalisée sur le développement de nouveaux marchés en Afrique du Nord et au Moyen-Orient.",
            'lucas.ferreira@twilio.com'      => "Head of Sales chez Twilio, je dirige les ventes EMEA pour les solutions de communication API.\nPassionné par les environnements hypercroissance et le management d'équipes commerciales internationales.",
            'camille.dupont@adobe.com'       => "Sales Manager chez Adobe, spécialisée dans les solutions Creative Cloud et Experience Cloud pour les agences.\nExperte en vente de solutions SaaS créatives et en accompagnement des équipes marketing.",
        ];

        foreach ($bios as $email => $bio) {
            $uid = DB::table('users')->where('email', $email)->value('id');
            if (!$uid) continue;
            DB::table('profiles')->where('user_id', $uid)->update([
                'bio'        => $bio,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void {}
};
