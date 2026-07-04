<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User  $user,
        public array $missing,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('profile_reminder', $this->vars());

        return new Envelope(subject: $resolved['subject'] ?? 'Complétez votre profil LeadXchange');
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('profile_reminder', $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.profile-reminder');
    }

    private function vars(): array
    {
        $pct = (10 - count($this->missing)) * 10;

        $missingHtml = '';
        if (!empty($this->missing)) {
            $missingHtml = '<ul style="margin:8px 0;padding-left:20px;">';
            foreach ($this->missing as $field) {
                $missingHtml .= '<li style="margin:4px 0;">' . htmlspecialchars($field['label'] ?? '') . '</li>';
            }
            $missingHtml .= '</ul>';
        }

        return [
            'name'           => $this->user->first_name,
            'completion_pct' => (string) $pct,
            'missing_fields' => $missingHtml,
            'profile_url'    => url('/profile/me'),
        ];
    }
}
