<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CguPageSeeder extends Seeder
{
    public function run(): void
    {
        $cgu = '<h2>1. Définitions</h2>
<ul>
<li><strong>« Application »</strong> : plateforme numérique LX (LeadsXchange), accessible via navigateur web et/ou application mobile.</li>
<li><strong>« Éditeur »</strong> : X-tensia, SAS, gestionnaire de l\'Application.</li>
<li><strong>« Utilisateur »</strong> : toute personne physique ou morale disposant d\'un compte actif.</li>
<li><strong>« Abonné »</strong> : Utilisateur ayant souscrit à un abonnement payant.</li>
<li><strong>« Lead »</strong> : information commerciale relative à un prospect, partagée entre Utilisateurs.</li>
<li><strong>« Prospect »</strong> : personne physique ou morale identifiée comme client potentiel.</li>
<li><strong>« Compte de Points »</strong> : solde de points personnel de chaque Abonné.</li>
<li><strong>« Score de Collaboration »</strong> : indicateur de réputation publique calculé sur l\'historique d\'échanges.</li>
<li><strong>« Communauté »</strong> : ensemble des Utilisateurs actifs inscrits sur l\'Application.</li>
</ul>
<h2>2. Objet et Champ d\'Application</h2>
<p>Les présentes CGU définissent les modalités et conditions dans lesquelles l\'Éditeur met à disposition des Utilisateurs l\'Application.</p>
<div class="lx-alert">⚠ L\'Application est <strong>exclusivement réservée aux professionnels (B2B)</strong>. En s\'inscrivant, l\'Utilisateur déclare agir dans le cadre de son activité professionnelle.</div>
<h2>3. Acceptation des CGU</h2>
<p>L\'accès implique l\'acceptation pleine des présentes CGU, matérialisée lors de l\'inscription. L\'Utilisateur déclare avoir la pleine capacité juridique et être âgé d\'au moins <strong>18 ans</strong>. L\'Éditeur peut modifier les CGU avec notification préalable.</p>
<h2>4. Accès au Service et Inscription</h2>
<p>Champs obligatoires : nom, prénom, email professionnel, téléphone, secteur d\'activité, fonction/poste, mot de passe. Chaque Utilisateur ne peut disposer que d\'<strong>un seul compte</strong>.</p>
<h2>5. Description du Service</h2>
<p>Fonctionnalités : mise en relation, échange de Leads, score de collaboration, messagerie, tableau de bord, recherche. Niveaux : Basic, Prémium, Consul, Ambassadeur, Entreprise. Service disponible <strong>24h/24 7j/7</strong>, maintenances annoncées 48h à l\'avance.</p>
<h2>6. Système de Points et de Réputation</h2>
<p>Chaque Abonné dispose d\'un Compte de Points : envoi accepté <strong>+2 pts</strong>, réception <strong>-1 pt</strong>, bonification post-RDV (MQL+1, SQL+3, SP+5). Délai de notation : 15 jours. Plafond : <strong>30 points</strong>. Malus si &gt;3 leads UQ sur 6 mois : -5 pts.</p>
<h2>7. Échange de Leads</h2>
<p>L\'émetteur doit avoir le <strong>consentement RGPD</strong> du Prospect ou une base légale. Les données sensibles (santé, mineurs…) sont interdites. Le récepteur s\'engage à utiliser les données uniquement à des fins professionnelles et à ne pas les revendre.</p>
<h2>8. Obligations des Utilisateurs</h2>
<p>Comportements interdits : harcèlement, fausses informations, usurpation d\'identité, spam, leads fictifs, scraping, concurrence déloyale. Tout manquement peut entraîner la suspension du compte.</p>
<h2>9. Propriété Intellectuelle</h2>
<p>L\'ensemble des éléments de l\'Application sont la <strong>propriété exclusive de l\'Éditeur</strong> (X-tensia). L\'Utilisateur bénéficie d\'une licence d\'utilisation personnelle, non exclusive et révocable. Toute reproduction, décompilation ou exploitation commerciale est interdite sans autorisation préalable.</p>
<p>Les Utilisateurs conservent la propriété de leurs contenus mais accordent à l\'Éditeur une licence mondiale et non exclusive pour les utiliser dans le cadre du fonctionnement de l\'Application.</p>
<h2>10. Protection des Données Personnelles</h2>
<p>Responsable de Traitement : <strong>X-tensia SAS</strong>, 144 avenue Charles De Gaulle, 92200 Neuilly-sur-Seine. Contact DPO : <a href="mailto:contact@leadxchange.com">contact@leadxchange.com</a>. Voir <a href="/legal/privacy">Politique de Confidentialité</a>.</p>
<h2>11. Confidentialité</h2>
<p>Obligation de confidentialité pendant la durée d\'utilisation + <strong>3 ans</strong> suivant la résiliation.</p>
<h2>12. Conditions Financières</h2>
<p>Paiement par carte bancaire sécurisée (PCI-DSS). Modification des tarifs avec préavis de <strong>30 jours</strong>. Résiliation possible à tout moment, effective en fin de période.</p>
<h2>13. Suspension et Résiliation</h2>
<p>Résiliation utilisateur : via les paramètres du compte. Résiliation Éditeur : en cas de violation des CGU, fraude, non-paiement. En cas de résiliation, le Compte de Points est définitivement clôturé.</p>
<h2>14. Responsabilité de l\'Éditeur</h2>
<p>L\'Éditeur agit en qualité d\'intermédiaire technique. Sa responsabilité est plafonnée aux sommes versées au cours des <strong>12 derniers mois</strong>.</p>
<h2>15. Cookies</h2>
<p>Cookies essentiels, analytiques et marketing utilisés. Gestion via le bandeau de consentement.</p>
<h2>16. Droit Applicable</h2>
<p>Droit <strong>français</strong> applicable. Juridiction compétente : <strong>Tribunaux de Nanterre</strong>. Résolution amiable privilégiée.</p>
<h2>17. Contact</h2>
<p>X-tensia SAS — 144 avenue Charles De Gaulle, 92200 Neuilly-sur-Seine<br>
Email : <a href="mailto:contact@leadxchange.com">contact@leadxchange.com</a><br>
RCS Nanterre : 999 916 190 — Capital : 5 000 €<br>
<em>Version 1.1 — 12/06/2026</em></p>';

        DB::table('pages')->where('slug', 'cgu')->update([
            'title'      => 'Conditions Générales d\'Utilisation — LeadXchange',
            'content'    => $cgu,
            'updated_at' => '2026-06-12 00:00:00',
        ]);

        $privacy = '<h2>Politique de Confidentialité</h2>
<p>Cette page sera complétée lors de la mise en œuvre de la Section 10 (RGPD).</p>
<p>Contact : <a href="mailto:contact@leadxchange.com">contact@leadxchange.com</a></p>';

        DB::table('pages')->where('slug', 'privacy')->update([
            'title'      => 'Politique de Confidentialité — LeadXchange',
            'content'    => $privacy,
            'updated_at' => now(),
        ]);
    }
}
