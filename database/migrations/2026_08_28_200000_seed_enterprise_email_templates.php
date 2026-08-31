<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('email_templates')->insertOrIgnore([

            // ── Proposition Pack Entreprise ──────────────────────────────────
            [
                'key'       => 'enterprise_proposal',
                'name'      => 'Proposition Pack Entreprise',
                'subject'   => 'Votre proposition Pack Entreprise — {{company_name}}',
                'variables' => json_encode(['name', 'company_name', 'plan_label', 'seats', 'duration_months', 'price', 'proposal_message', 'proposal_url']),
                'body'      => '<p class="greeting">Bonjour {{name}} 👋</p>

<p class="text">Suite à votre demande, nous avons le plaisir de vous adresser notre proposition de <strong>Pack Entreprise</strong> pour <strong>{{company_name}}</strong>.</p>

<table style="border-collapse:collapse;width:100%;max-width:480px;margin:24px auto;border-radius:10px;overflow:hidden;border:1px solid #E5E7EE;">
  <tr style="background:#F9FAFB;">
    <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #E5E7EE;font-weight:600;">Plan</td>
    <td style="padding:12px 16px;font-size:13px;color:#111827;border-bottom:1px solid #E5E7EE;">{{plan_label}}</td>
  </tr>
  <tr>
    <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #E5E7EE;font-weight:600;">Licences</td>
    <td style="padding:12px 16px;font-size:13px;color:#111827;border-bottom:1px solid #E5E7EE;">{{seats}} utilisateurs</td>
  </tr>
  <tr style="background:#F9FAFB;">
    <td style="padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #E5E7EE;font-weight:600;">Durée</td>
    <td style="padding:12px 16px;font-size:13px;color:#111827;border-bottom:1px solid #E5E7EE;">{{duration_months}} mois</td>
  </tr>
  <tr>
    <td style="padding:12px 16px;font-size:14px;color:#374151;font-weight:600;">Prix total</td>
    <td style="padding:12px 16px;font-size:16px;font-weight:700;color:#14A98C;">{{price}}</td>
  </tr>
</table>

{{proposal_message_block}}

<p class="text">Consultez votre proposition détaillée et procédez au paiement sécurisé en cliquant sur le bouton ci-dessous.</p>

<div style="text-align:center;margin:28px 0;">
  <a href="{{proposal_url}}" class="btn" style="background-color:#6366F1;">Voir la proposition et payer</a>
</div>

<div class="divider"></div>
<p style="font-size:12px;color:#9CA3AF;">Ce lien est personnel et sécurisé. Ne le partagez pas. En cas de question, contactez-nous à <a href="mailto:contact@leadxchange.com">contact@leadxchange.com</a>.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ── Demande acceptée (converted) ─────────────────────────────────
            [
                'key'       => 'enterprise_quote_accepted',
                'name'      => 'Demande Pack Entreprise acceptée',
                'subject'   => 'Votre demande Pack Entreprise a été acceptée — LeadXchange',
                'variables' => json_encode(['name', 'company_name', 'dashboard_url']),
                'body'      => '<p class="greeting">Bonne nouvelle, {{name}} ! 🎉</p>

<p class="text">Votre demande de <strong>Pack Entreprise</strong> pour <strong>{{company_name}}</strong> a été <strong>acceptée</strong> par notre équipe.</p>

<div class="info-card">
  <p style="font-size:14px;color:#0B6B5A;margin:0;"><strong>Prochaine étape :</strong> Notre équipe va vous contacter très prochainement pour finaliser les détails et mettre en place votre espace entreprise.</p>
</div>

<div style="text-align:center;margin:28px 0;">
  <a href="{{dashboard_url}}" class="btn">Accéder à mon espace</a>
</div>

<div class="divider"></div>
<p style="font-size:12px;color:#9CA3AF;">Merci de votre confiance. L\'équipe LeadXchange.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ── Demande non retenue (closed) ─────────────────────────────────
            [
                'key'       => 'enterprise_quote_rejected',
                'name'      => 'Demande Pack Entreprise non retenue',
                'subject'   => 'Votre demande Pack Entreprise — LeadXchange',
                'variables' => json_encode(['name', 'company_name', 'admin_notes', 'dashboard_url']),
                'body'      => '<p class="greeting">Bonjour {{name}},</p>

<p class="text">Nous vous remercions de l\'intérêt que vous portez à <strong>LeadXchange</strong>.</p>

<p class="text">Après étude de votre dossier, nous ne sommes malheureusement pas en mesure de donner suite à votre demande de <strong>Pack Entreprise</strong> pour <strong>{{company_name}}</strong> pour le moment.</p>

{{admin_notes_block}}

<p class="text">N\'hésitez pas à nous contacter pour plus d\'informations ou pour toute question à l\'adresse <a href="mailto:contact@leadxchange.com">contact@leadxchange.com</a>.</p>

<div style="text-align:center;margin:28px 0;">
  <a href="{{dashboard_url}}" class="btn">Retour à mon espace</a>
</div>

<div class="divider"></div>
<p style="font-size:12px;color:#9CA3AF;">L\'équipe LeadXchange reste à votre disposition pour toute question.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', [
            'enterprise_proposal',
            'enterprise_quote_accepted',
            'enterprise_quote_rejected',
        ])->delete();
    }
};
