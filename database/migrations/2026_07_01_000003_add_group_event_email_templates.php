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
                'key'       => 'new_group',
                'name'      => 'Nouveau groupe dans votre région',
                'subject'   => 'Nouveau groupe LeadXchange : {{group_name}}',
                'variables' => json_encode(['name', 'group_name', 'group_description', 'group_url', 'city', 'sector', 'creator_name']),
                'body'      => '<p class="greeting">Bonjour {{name}} 👋</p>
<p class="text">Un nouveau groupe vient d\'être créé sur LeadXchange dans votre réseau. Il pourrait correspondre à vos intérêts professionnels !</p>

<div class="info-card">
    <p style="font-size:19px; font-weight:700; color:#0F766E; margin:0 0 8px;">{{group_name}}</p>
    <p style="font-size:14px; line-height:1.6; color:#374151; margin:0 0 14px;">{{group_description}}</p>
    <span class="tag" style="background:#CCFBF1; color:#0F766E;">📍 {{city}}</span>
    <span class="tag" style="background:#EEF2FF; color:#4F46E5;">💼 {{sector}}</span>
</div>

<div style="text-align:center; margin:28px 0;">
    <a href="{{group_url}}" class="btn btn-teal" style="display:inline-block; background-color:#0D9488; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:14px;">
        Voir le groupe →
    </a>
</div>

<div class="divider"></div>
<p class="text" style="font-size:13px; color:#6B7280;">Créé par <strong>{{creator_name}}</strong>. Si ce groupe ne correspond pas à vos attentes, vous pouvez simplement ignorer cet email.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'       => 'new_event',
                'name'      => 'Nouvel événement dans votre région',
                'subject'   => 'Événement à venir : {{event_title}}',
                'variables' => json_encode(['name', 'event_title', 'event_description', 'event_url', 'city', 'starts_at', 'event_type', 'creator_name', 'price_label']),
                'body'      => '<p class="greeting">Bonjour {{name}} 👋</p>
<p class="text">Un nouvel événement vient d\'être organisé dans votre région sur LeadXchange. Rejoignez-le avant qu\'il ne soit complet !</p>

<div class="info-card info-card-blue">
    <p style="font-size:19px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">{{event_title}}</p>
    <p style="font-size:14px; line-height:1.6; color:#374151; margin:0 0 14px;">{{event_description}}</p>
    <span class="tag" style="background:#DBEAFE; color:#1D4ED8;">📅 {{starts_at}}</span>
    <span class="tag" style="background:#CCFBF1; color:#0F766E;">📍 {{city}}</span>
    <span class="tag" style="background:#F3E8FF; color:#7C3AED;">{{event_type}}</span>
    <br>
    <p style="margin:10px 0 0; font-size:14px; font-weight:700; color:#374151;">{{price_label}}</p>
</div>

<div style="text-align:center; margin:28px 0;">
    <a href="{{event_url}}" class="btn" style="display:inline-block; background-color:#4338CA; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-weight:600; font-size:14px;">
        S\'inscrire à l\'événement →
    </a>
</div>

<div class="divider"></div>
<p class="text" style="font-size:13px; color:#6B7280;">Organisé par <strong>{{creator_name}}</strong>. Si cet événement ne vous intéresse pas, vous pouvez ignorer cet email.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('key', ['new_group', 'new_event'])->delete();
    }
};
