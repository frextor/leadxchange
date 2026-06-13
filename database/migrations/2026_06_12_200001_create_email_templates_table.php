<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('email_templates')->insertOrIgnore([
            [
                'key'       => 'verification',
                'name'      => 'Vérification email',
                'subject'   => 'Vérifiez votre adresse email — LeadXchange',
                'variables' => json_encode(['name', 'verification_url']),
                'body'      => '<p class="greeting">Bonjour {{name}} 👋</p>
<p class="text">Merci de vous être inscrit(e) sur <strong>LeadXchange</strong> ! Pour activer votre compte, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous.</p>
<div style="text-align:center;">
    <a href="{{verification_url}}" class="btn">Vérifier mon adresse email</a>
</div>
<p class="text" style="font-size:13px; color:#6B7280;">Ce lien est valable pendant <strong>60 minutes</strong>. Si vous n\'avez pas créé de compte, vous pouvez ignorer cet email.</p>
<div class="divider"></div>
<p style="font-size:12px; color:#9CA3AF;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
<span style="color:#6366F1; word-break:break-all;">{{verification_url}}</span></p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'       => 'password_reset',
                'name'      => 'Réinitialisation mot de passe',
                'subject'   => 'Réinitialisation de votre mot de passe — LeadXchange',
                'variables' => json_encode(['name', 'reset_url', 'expires_in']),
                'body'      => '<p class="greeting">Bonjour {{name}},</p>
<p class="text">Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte LeadXchange. Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.</p>
<div style="text-align:center;">
    <a href="{{reset_url}}" class="btn">Réinitialiser mon mot de passe</a>
</div>
<p class="text" style="font-size:13px; color:#6B7280;">Ce lien expirera dans <strong>{{expires_in}} minutes</strong>. Si vous n\'avez pas demandé de réinitialisation, ignorez cet email — votre mot de passe actuel reste inchangé.</p>
<div class="divider"></div>
<p style="font-size:12px; color:#9CA3AF;">Si le bouton ne fonctionne pas :<br>
<span style="color:#6366F1; word-break:break-all;">{{reset_url}}</span></p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'       => 'system_notification',
                'name'      => 'Notification système',
                'subject'   => '{{title}} — LeadXchange',
                'variables' => json_encode(['name', 'title', 'body', 'action_label', 'action_url']),
                'body'      => '<p class="greeting">Bonjour {{name}},</p>
<p class="text">{{body}}</p>
<div style="text-align:center;">
    <a href="{{action_url}}" class="btn">{{action_label}}</a>
</div>
<div class="divider"></div>
<p style="font-size:12px; color:#9CA3AF;">Cet email a été envoyé automatiquement par la plateforme LeadXchange.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'       => 'marketing',
                'name'      => 'Email marketing',
                'subject'   => '{{headline}} — LeadXchange',
                'variables' => json_encode(['name', 'headline', 'body', 'cta_label', 'cta_url']),
                'body'      => '<p class="greeting">{{name}},</p>
<p style="font-size:22px; font-weight:800; color:#111827; margin-bottom:12px;">{{headline}}</p>
<p class="text">{{body}}</p>
<div style="text-align:center;">
    <a href="{{cta_url}}" class="btn">{{cta_label}}</a>
</div>
<div class="divider"></div>
<p style="font-size:11px; color:#9CA3AF; text-align:center;">Vous recevez cet email car vous êtes abonné(e) aux communications LeadXchange.</p>',
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
