<?php

namespace App\Mail;

use App\Models\EnterpriseInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnterpriseInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EnterpriseInvitation $invitation,
        public string $plainToken,
    ) {}

    public function build(): self
    {
        $url = url('/enterprise/invitations/' . $this->plainToken);
        $ownerName = trim($this->invitation->owner->first_name . ' ' . $this->invitation->owner->last_name);
        $expiresAt = optional($this->invitation->expires_at)->format('d/m/Y');

        return $this
            ->subject('You are invited to join LeadXchange Enterprise')
            ->html(<<<HTML
<!doctype html>
<html>
<body style="font-family: Arial, sans-serif; color: #0D2B45; line-height: 1.5;">
    <h1 style="font-size: 22px;">Invitation LeadXchange Entreprise</h1>
    <p>{$ownerName} vous invite à rejoindre LeadXchange avec le pack Entreprise.</p>
    <p>Ce pack vous donne accès aux avantages de l’abonnement entreprise sans paiement individuel.</p>
    <p>
        <a href="{$url}" style="display: inline-block; padding: 12px 18px; background: #1E8F88; color: #ffffff; text-decoration: none; border-radius: 8px;">
            Rejoindre LeadXchange
        </a>
    </p>
    <p style="font-size: 13px; color: #5f6f7f;">Ce lien expire le {$expiresAt}.</p>
</body>
</html>
HTML);
    }
}
