<?php

namespace App\Mail;

use App\Models\EnterpriseInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnterpriseInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EnterpriseInvitation $invitation,
        public readonly string $holderName,
        public readonly ?string $tempPassword = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->holderName . ' vous invite à rejoindre son équipe LeadXchange',
        );
    }

    public function content(): Content
    {
        $joinUrl = $this->invitation->joinUrl();
        $isNew   = $this->tempPassword !== null;

        $credentialsBlock = $isNew ? "
            <div style='background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:14px 18px;margin:16px 0;'>
                <p style='margin:0 0 6px;font-weight:600;color:#166534;font-size:13px;'>Vos identifiants provisoires</p>
                <p style='margin:0;font-size:13px;color:#15803D;'>Email : <strong>{$this->invitation->email}</strong></p>
                <p style='margin:4px 0 0;font-size:13px;color:#15803D;'>Mot de passe temporaire : <strong>{$this->tempPassword}</strong></p>
            </div>" : '';

        $body = "
            <p style='font-size:15px;'>Bonjour,</p>
            <p><strong>{$this->holderName}</strong> vous invite à rejoindre son équipe sur <strong>LeadXchange</strong> avec le <strong>Pack Entreprise</strong>.</p>
            <p>Cette invitation vous donne accès à toutes les fonctionnalités Premium sans paiement individuel.</p>
            {$credentialsBlock}
            <p style='text-align:center;margin:24px 0;'>
                <a href='{$joinUrl}'
                   style='display:inline-block;padding:13px 28px;background:linear-gradient(135deg,#1E8F88,#14A98C);color:#fff;font-weight:600;font-size:14px;text-decoration:none;border-radius:12px;'>
                    Rejoindre l'équipe →
                </a>
            </p>
            <p style='font-size:12px;color:#94A3B8;'>Si vous ne souhaitez pas rejoindre, ignorez cet e-mail. Ce lien est à usage unique.</p>
        ";

        return new Content(view: 'emails.db_template', with: ['content' => $body]);
    }
}
