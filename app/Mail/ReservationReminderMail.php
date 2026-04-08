<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
        public readonly string $type, // '24h' | '2h'
    ) {}

    public function envelope(): Envelope
    {
        $when = $this->type === '24h' ? 'mañana' : 'en 2 horas';

        return new Envelope(
            subject: "Recordatorio: tu reservación es {$when} — {$this->reservation->restaurant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-reminder',
        );
    }
}
