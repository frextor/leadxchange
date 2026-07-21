<?php

namespace App\Mail;

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
        return new Envelope(
            subject: $this->referrer->first_name . ' ' . $this->referrer->last_name . ' t\'invite à rejoindre LeadXchange',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.referral_invitation',
        );
    }
}
