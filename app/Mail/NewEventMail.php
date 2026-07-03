<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly User  $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('new_event', $this->vars());
        $subject  = $resolved['subject'] ?? ('Événement à venir : ' . $this->event->title);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('new_event', $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.new_event', with: $this->vars());
    }

    private function vars(): array
    {
        $this->event->loadMissing(['city', 'sector', 'creator']);

        $typeLabels = [
            'virtual'    => 'En ligne',
            'in_person'  => 'En présentiel',
            'hybrid'     => 'Hybride',
        ];

        $priceLabel = ($this->event->price && (float) $this->event->price > 0)
            ? number_format((float) $this->event->price, 2, ',', ' ') . ' €'
            : 'Gratuit';

        return [
            'name'              => $this->recipient->first_name,
            'event_title'       => $this->event->title,
            'event_description' => $this->event->description ?? 'Aucune description.',
            'event_url'         => route('events.show', $this->event->id),
            'city'              => $this->event->city?->name ?? 'Non précisé',
            'starts_at'         => $this->event->starts_at?->translatedFormat('d F Y à H\hi') ?? 'À définir',
            'event_type'        => $typeLabels[$this->event->type] ?? $this->event->type,
            'creator_name'      => $this->event->creator?->first_name . ' ' . $this->event->creator?->last_name,
            'price_label'       => $priceLabel,
        ];
    }
}
