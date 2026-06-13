<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $resetUrl,
        public readonly int    $expiresInMinutes = 60,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('password_reset', $this->vars());

        return new Envelope(subject: $resolved['subject'] ?? 'Réinitialisation de votre mot de passe — LeadXchange');
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('password_reset', $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.password_reset');
    }

    private function vars(): array
    {
        return [
            'name'       => $this->user->first_name,
            'reset_url'  => $this->resetUrl,
            'expires_in' => (string) $this->expiresInMinutes,
        ];
    }
}
