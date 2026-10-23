<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\PromoCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromoCodeInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PromoCode $promoCode,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('promo_code_invitation', $this->vars());

        return new Envelope(
            subject: $resolved['subject'] ?? 'Un accès Premium vous a été offert — LeadXchange',
        );
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('promo_code_invitation', $this->vars());

        if ($resolved) {
            return new Content(
                view: 'emails.db_template',
                with: ['content' => $resolved['body']],
            );
        }

        return new Content(view: 'emails.promo_code_invitation', with: $this->vars());
    }

    private function vars(): array
    {
        return [
            'code'         => $this->promoCode->code,
            'premium_days' => (string) $this->promoCode->premium_days,
            'redeem_url'   => route('promo.redeem'),
        ];
    }
}
