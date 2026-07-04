<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string  $recipientName,
        public readonly string  $title,
        public readonly string  $body,
        public readonly ?string $actionLabel = null,
        public readonly ?string $actionUrl   = null,
        public readonly string  $templateKey = 'system_notification',
        public readonly array   $extraVars   = [],
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve($this->templateKey, $this->vars());

        return new Envelope(subject: $resolved['subject'] ?? ($this->title . ' — LeadXchange'));
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve($this->templateKey, $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.system_notification');
    }

    private function vars(): array
    {
        $url = $this->actionUrl ?? '#';

        return array_merge([
            'name'          => $this->recipientName,
            'title'         => $this->title,
            'body'          => $this->body,
            'action_label'  => $this->actionLabel ?? '',
            'action_url'    => $url,
            'dashboard_url' => $url,
            'profile_url'   => $url,
        ], $this->extraVars);
    }
}
