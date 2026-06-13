<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $recipientName,
        public readonly string  $subject,
        public readonly string  $headline,
        public readonly string  $body,
        public readonly ?string $ctaLabel = null,
        public readonly ?string $ctaUrl   = null,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('marketing', $this->vars());

        return new Envelope(subject: $resolved['subject'] ?? $this->subject);
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('marketing', $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.marketing');
    }

    private function vars(): array
    {
        return [
            'name'      => $this->recipientName,
            'headline'  => $this->headline,
            'body'      => $this->body,
            'cta_label' => $this->ctaLabel ?? '',
            'cta_url'   => $this->ctaUrl ?? '#',
        ];
    }
}
