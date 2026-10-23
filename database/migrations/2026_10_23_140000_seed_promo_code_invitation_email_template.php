<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $body = <<<'HTML'
<div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;max-width:560px;margin:0 auto;">

  <div style="background:linear-gradient(135deg,#0D2B45,#0B6E6A);padding:32px 40px;text-align:center;border-radius:16px 16px 0 0;">
    <p style="margin:0;color:#ffffff;font-size:22px;font-weight:700;letter-spacing:-0.5px;">LeadXchange</p>
    <p style="margin:6px 0 0;color:rgba(255,255,255,0.75);font-size:13px;">Le réseau des professionnels B2B</p>
  </div>

  <div style="background:#ffffff;padding:36px 40px;">
    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#1E8F88;text-transform:uppercase;letter-spacing:1px;">Cadeau LeadXchange</p>
    <h1 style="margin:0 0 20px;font-size:24px;font-weight:700;color:#0D2B45;line-height:1.3;">
      {{premium_days}} jours de Premium vous sont offerts
    </h1>

    <p style="margin:0 0 24px;font-size:15px;color:#4A5568;line-height:1.6;">
      Utilisez le code ci-dessous sur votre compte LeadXchange pour profiter de toutes les fonctionnalités Premium gratuitement pendant {{premium_days}} jours.
    </p>

    <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
      <tr>
        <td style="background:#F0FDFA;border:2px dashed #1E8F88;border-radius:12px;padding:16px 36px;">
          <p style="margin:0;font-size:22px;font-weight:700;letter-spacing:3px;color:#0D2B45;font-family:'Courier New',monospace;text-align:center;">{{code}}</p>
        </td>
      </tr>
    </table>

    <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
      <tr>
        <td style="background:linear-gradient(135deg,#1E8F88,#0B6E6A);border-radius:12px;">
          <a href="{{redeem_url}}"
             style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;border-radius:12px;">
            Activer mon code
          </a>
        </td>
      </tr>
    </table>

    <p style="margin:0;font-size:12px;color:#9BA8B7;text-align:center;">
      Ce code est à usage unique.
    </p>
  </div>

  <div style="background:#F0F4F8;padding:20px 40px;text-align:center;border-top:1px solid #E8EDF2;border-radius:0 0 16px 16px;">
    <p style="margin:0;font-size:11px;color:#9BA8B7;">
      © LeadXchange. Tous droits réservés.
    </p>
  </div>

</div>
HTML;

        DB::table('email_templates')->upsert([
            [
                'key'        => 'promo_code_invitation',
                'name'       => 'Code promo offert',
                'subject'    => '{{premium_days}} jours de Premium vous sont offerts — LeadXchange',
                'body'       => $body,
                'variables'  => json_encode(['code', 'premium_days', 'redeem_url']),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['key'], ['name', 'subject', 'body', 'variables', 'updated_at']);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('key', 'promo_code_invitation')->delete();
    }
};
