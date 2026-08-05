<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $referrer,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('referral_invitation', $this->vars());

        $defaultSubject = $this->referrer->first_name . ' ' . $this->referrer->last_name
            . ' vous invite à rejoindre LeadXchange';

        return new Envelope(subject: $resolved['subject'] ?? $defaultSubject);
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('referral_invitation', $this->vars());

        if ($resolved) {
            return new Content(
                view: 'emails.db_template',
                with: ['content' => $resolved['body']],
            );
        }

        // Fallback sur la vue blade statique
        return new Content(view: 'emails.referral_invitation');
    }

    private function vars(): array
    {
        return [
            'referrer_name' => $this->referrer->first_name . ' ' . $this->referrer->last_name,
            'register_url'  => route('referral.register', ['token' => $this->token]),
        ];
    }
}
