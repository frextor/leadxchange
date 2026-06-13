<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('verification', $this->vars());

        return new Envelope(subject: $resolved['subject'] ?? 'Vérifiez votre adresse email — LeadXchange');
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('verification', $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.verification');
    }

    private function vars(): array
    {
        return [
            'name'             => $this->user->first_name,
            'verification_url' => $this->verificationUrl,
        ];
    }
}
