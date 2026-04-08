<?php

namespace App\Mail;

use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistNotifiedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Waitlist $waitlist) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Hay disponibilidad para tu fecha! — {$this->waitlist->restaurant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.waitlist-notified',
        );
    }
}
