<?php

namespace App\Mail;

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
        return new Envelope(subject: 'Complétez votre profil LeadXchange');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.profile-reminder');
    }
}
