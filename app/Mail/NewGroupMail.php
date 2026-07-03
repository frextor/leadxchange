<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewGroupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Group $group,
        public readonly User  $recipient,
    ) {}

    public function envelope(): Envelope
    {
        $resolved = EmailTemplate::resolve('new_group', $this->vars());
        $subject  = $resolved['subject'] ?? ('Nouveau groupe LeadXchange : ' . $this->group->name);

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $resolved = EmailTemplate::resolve('new_group', $this->vars());

        if ($resolved) {
            return new Content(view: 'emails.db_template', with: ['content' => $resolved['body']]);
        }

        return new Content(view: 'emails.new_group', with: $this->vars());
    }

    private function vars(): array
    {
        $this->group->loadMissing(['city', 'sector', 'creator']);

        return [
            'name'              => $this->recipient->first_name,
            'group_name'        => $this->group->name,
            'group_description' => $this->group->description ?? 'Aucune description.',
            'group_url'         => route('groups.show', $this->group->id),
            'city'              => $this->group->city?->name ?? 'Non précisé',
            'sector'            => $this->group->sector?->name ?? 'Tous secteurs',
            'creator_name'      => $this->group->creator?->first_name . ' ' . $this->group->creator?->last_name,
        ];
    }
}
