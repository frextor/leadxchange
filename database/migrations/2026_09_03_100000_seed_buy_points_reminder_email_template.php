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
                'key'       => 'buy_points_reminder',
                'name'      => 'Rappel achat de points',
                'subject'   => '{{name}}, votre solde de points est faible — LeadXchange',
                'variables' => json_encode(['name', 'balance', 'points_url']),
                'body'      => '<p class="greeting">Bonjour {{name}} 👋</p>

<p class="text">Nous avons remarqué que votre solde de points LeadXchange est actuellement de <strong>{{balance}} point(s)</strong>.</p>

<div class="info-card" style="background:#FEF3C7;border-color:#FDE68A;">
  <p style="font-size:13px;color:#92400E;margin:0 0 6px;font-weight:700;">⭐ Vos points, à quoi servent-ils ?</p>
  <ul style="font-size:13px;color:#78350F;margin:0;padding-left:18px;line-height:1.8;">
    <li>Envoyer des leads à vos contacts</li>
    <li>Accéder aux fonctionnalités premium de mise en relation</li>
    <li>Booster votre visibilité sur la plateforme</li>
  </ul>
</div>

<p class="text">Rechargez votre compte dès maintenant pour continuer à profiter pleinement de LeadXchange et ne rater aucune opportunité professionnelle.</p>

<div style="text-align:center;margin:28px 0;">
  <a href="{{points_url}}" class="btn" style="background:linear-gradient(135deg,#6366F1,#4F46E5);">⭐ Acheter des points</a>
</div>

<div class="divider"></div>
<p style="font-size:12px;color:#9CA3AF;">Si vous avez des questions, notre équipe est à votre disposition. L\'équipe LeadXchange.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'buy_points_reminder')->delete();
    }
};
