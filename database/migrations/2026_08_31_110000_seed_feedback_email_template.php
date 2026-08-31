<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('email_templates')->insertOrIgnore([
            [
                'key'       => 'feedback_received',
                'name'      => 'Confirmation de feedback',
                'subject'   => 'Merci pour votre retour — LeadXchange',
                'variables' => json_encode(['name', 'message_excerpt', 'dashboard_url']),
                'body'      => '<p class="greeting">Merci {{name}} 🙏</p>

<p class="text">Nous avons bien reçu votre retour et nous vous en remercions sincèrement. Chaque avis compte et contribue à améliorer l\'expérience de tous les membres de <strong>LeadXchange</strong>.</p>

<div class="info-card">
  <p style="font-size:13px;color:#0B6B5A;margin:0 0 6px;font-weight:700;">Votre message :</p>
  <p style="font-size:13px;color:#0B6B5A;margin:0;font-style:italic;">« {{message_excerpt}} »</p>
</div>

<p class="text">Notre équipe prend le temps de lire chaque feedback avec attention. Si votre retour nécessite une réponse, nous reviendrons vers vous directement.</p>

<div style="text-align:center;margin:28px 0;">
  <a href="{{dashboard_url}}" class="btn">Accéder à mon espace</a>
</div>

<div class="divider"></div>
<p style="font-size:12px;color:#9CA3AF;">Merci de votre confiance. L\'équipe LeadXchange.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'feedback_received')->delete();
    }
};
