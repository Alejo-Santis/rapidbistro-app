<?php

namespace App\Console\Commands;

use App\Mail\ReservationReminderMail;
use App\Models\Reservation;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReservationReminders extends Command
{
    protected $signature   = 'reservations:send-reminders';
    protected $description = 'Envía recordatorios por email 24h y 2h antes de cada reservación';

    public function handle(WhatsAppService $whatsapp): int
    {
        $sent = 0;

        // Recordatorios 24 horas antes
        $tomorrow = now()->addDay()->toDateString();
        $hour24   = now()->addDay()->format('H:i');

        $reservations24h = Reservation::with(['restaurant', 'table.zone'])
            ->whereDate('reservation_date', $tomorrow)
            ->whereRaw("TO_CHAR(starts_at::time, 'HH24:MI') = ?", [$hour24])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('guest_email')
            ->get();

        foreach ($reservations24h as $reservation) {
            Mail::to($reservation->guest_email)->send(new ReservationReminderMail($reservation, '24h'));
            $whatsapp->sendReminder($reservation, '24h');
            $sent++;
            $this->line("  [24h] {$reservation->guest_name} <{$reservation->guest_email}>");
        }

        // Recordatorios 2 horas antes
        $today  = now()->toDateString();
        $hour2  = now()->addHours(2)->format('H:i');

        $reservations2h = Reservation::with(['restaurant', 'table.zone'])
            ->whereDate('reservation_date', $today)
            ->whereRaw("TO_CHAR(starts_at::time, 'HH24:MI') = ?", [$hour2])
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('guest_email')
            ->get();

        foreach ($reservations2h as $reservation) {
            Mail::to($reservation->guest_email)->send(new ReservationReminderMail($reservation, '2h'));
            $whatsapp->sendReminder($reservation, '2h');
            $sent++;
            $this->line("  [2h]  {$reservation->guest_name} <{$reservation->guest_email}>");
        }

        $this->info("Recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }
}
