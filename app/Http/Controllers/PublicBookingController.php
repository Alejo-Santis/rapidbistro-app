<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicReservationRequest;
use App\Http\Requests\StoreWaitlistRequest;
use App\Mail\ReservationConfirmedMail;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\TimeSlot;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PublicBookingController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::where('is_active', true)->firstOrFail();

        return Inertia::render('Booking/Index', [
            'restaurant' => [
                'name'    => $restaurant->name,
                'address' => $restaurant->address,
                'phone'   => $restaurant->phone,
                'email'   => $restaurant->email,
                'policy'  => $restaurant->settings['cancellation_policy'] ?? null,
            ],
            'minDate' => now()->toDateString(),
            'maxDate' => now()->addDays(60)->toDateString(),
        ]);
    }

    public function availability(Request $request)
    {
        $request->validate([
            'date'       => ['required', 'date', 'after_or_equal:today'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $date      = $request->date;
        $partySize = (int) $request->party_size;
        $dayOfWeek = strtolower(date('l', strtotime($date)));

        $restaurant = Restaurant::where('is_active', true)->firstOrFail();

        $timeSlots = TimeSlot::where('restaurant_id', $restaurant->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('opens_at')
            ->get();

        if ($timeSlots->isEmpty()) {
            return response()->json(['available' => [], 'closed' => true]);
        }

        $availableTablesCount = Table::whereHas('zone', fn ($q) => $q->where('is_active', true))
            ->where('capacity', '>=', $partySize)
            ->count();

        if ($availableTablesCount === 0) {
            return response()->json(['available' => [], 'no_tables' => true]);
        }

        $slots = [];

        foreach ($timeSlots as $slot) {
            $openMin  = $this->toMinutes($slot->opens_at);
            $closeMin = $this->toMinutes($slot->closes_at);
            $duration = $slot->slot_duration_minutes;

            // Generar cada bloque de tiempo dentro del horario del turno
            for ($start = $openMin; $start + $duration <= $closeMin; $start += $duration) {
                $startsAt = sprintf('%02d:%02d', intdiv($start, 60), $start % 60);
                $endsAt   = sprintf('%02d:%02d', intdiv($start + $duration, 60), ($start + $duration) % 60);

                // Contar mesas disponibles para este bloque
                $reserved = Reservation::whereDate('reservation_date', $date)
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->where(fn ($q) => $q
                        ->where('starts_at', '<', $endsAt . ':00')
                        ->where('ends_at', '>', $startsAt . ':00')
                    )
                    ->pluck('table_id');

                $freeTables = Table::whereHas('zone', fn ($q) => $q->where('is_active', true))
                    ->where('capacity', '>=', $partySize)
                    ->whereNotIn('id', $reserved)
                    ->whereNotIn('status', ['maintenance', 'unavailable'])
                    ->count();

                if ($freeTables > 0) {
                    $slots[] = [
                        'starts_at'   => $startsAt,
                        'ends_at'     => $endsAt,
                        'service'     => $slot->name,
                        'free_tables' => $freeTables,
                    ];
                }
            }
        }

        return response()->json(['available' => $slots]);
    }

    public function store(StorePublicReservationRequest $request)
    {
        $validated = $request->validated();
        $restaurant = Restaurant::where('is_active', true)->firstOrFail();

        $dayOfWeek = strtolower(date('l', strtotime($validated['reservation_date'])));

        $slot = TimeSlot::where('restaurant_id', $restaurant->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->where('opens_at', '<=', $validated['starts_at'] . ':00')
            ->where('closes_at', '>', $validated['starts_at'] . ':00')
            ->first();

        $duration = $slot?->slot_duration_minutes ?? 90;
        [$h, $m]  = explode(':', $validated['starts_at']);
        $endMins  = ((int)$h * 60 + (int)$m) + $duration;
        $endsAt   = sprintf('%02d:%02d', intdiv($endMins, 60), $endMins % 60);

        // ── Transacción con lock para evitar double-booking ──────────────
        $reservation = DB::transaction(function () use ($validated, $restaurant, $endsAt) {

            // Obtener tablas con lock para que otras transacciones concurrentes
            // esperen antes de leer disponibilidad
            $reserved = Reservation::whereDate('reservation_date', $validated['reservation_date'])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->where(fn ($q) => $q
                    ->where('starts_at', '<', $endsAt . ':00')
                    ->where('ends_at', '>', $validated['starts_at'] . ':00')
                )
                ->lockForUpdate()
                ->pluck('table_id');

            $table = Table::whereHas('zone', fn ($q) => $q->where('is_active', true))
                ->where('capacity', '>=', $validated['party_size'])
                ->whereNotIn('id', $reserved)
                ->whereNotIn('status', ['maintenance', 'unavailable'])
                ->orderBy('capacity')
                ->lockForUpdate()
                ->first();

            if (! $table) {
                return null;
            }

            return Reservation::create([
                'restaurant_id'    => $restaurant->id,
                'table_id'         => $table->id,
                'reservation_date' => $validated['reservation_date'],
                'starts_at'        => $validated['starts_at'],
                'ends_at'          => $endsAt,
                'party_size'       => $validated['party_size'],
                'guest_name'       => $validated['guest_name'],
                'guest_email'      => $validated['guest_email'],
                'guest_phone'      => $validated['guest_phone'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'confirmed',
            ]);
        });

        if (! $reservation) {
            return back()->withErrors(['starts_at' => 'Lo sentimos, ya no hay mesas disponibles para ese horario.']);
        }

        $reservation->load(['restaurant', 'table.zone']);

        Mail::to($reservation->guest_email)->send(new ReservationConfirmedMail($reservation));

        return redirect()->route('booking.confirmation', $reservation->confirmation_code);
    }

    public function confirmation(string $code)
    {
        $reservation = Reservation::where('confirmation_code', $code)
            ->with(['restaurant', 'table.zone'])
            ->firstOrFail();

        return Inertia::render('Booking/Confirmation', [
            'reservation' => [
                'confirmation_code' => $reservation->confirmation_code,
                'guest_name'        => $reservation->guest_name,
                'guest_email'       => $reservation->guest_email,
                'reservation_date'  => $reservation->reservation_date->format('d \d\e F \d\e Y'),
                'starts_at'         => substr($reservation->starts_at, 0, 5),
                'ends_at'           => substr($reservation->ends_at, 0, 5),
                'party_size'        => $reservation->party_size,
                'table_number'      => $reservation->table?->number,
                'zone_name'         => $reservation->table?->zone?->name,
                'notes'             => $reservation->notes,
                'status'            => $reservation->status,
            ],
            'restaurant' => [
                'name'    => $reservation->restaurant->name,
                'address' => $reservation->restaurant->address,
                'phone'   => $reservation->restaurant->phone,
                'email'   => $reservation->restaurant->email,
                'policy'  => $reservation->restaurant->settings['cancellation_policy'] ?? null,
            ],
        ]);
    }

    public function cancelForm(string $token)
    {
        $reservation = Reservation::where('cancellation_token', $token)
            ->with(['restaurant', 'table.zone'])
            ->firstOrFail();

        $canCancel = ! in_array($reservation->status, ['cancelled', 'completed', 'seated', 'no_show']);

        return Inertia::render('Booking/Cancel', [
            'reservation' => [
                'confirmation_code' => $reservation->confirmation_code,
                'guest_name'        => $reservation->guest_name,
                'reservation_date'  => $reservation->reservation_date->format('d \d\e F \d\e Y'),
                'starts_at'         => substr($reservation->starts_at, 0, 5),
                'ends_at'           => substr($reservation->ends_at, 0, 5),
                'party_size'        => $reservation->party_size,
                'status'            => $reservation->status,
            ],
            'restaurant' => [
                'name'  => $reservation->restaurant->name,
                'email' => $reservation->restaurant->email,
                'phone' => $reservation->restaurant->phone,
            ],
            'canCancel' => $canCancel,
            'token'     => $token,
        ]);
    }

    public function cancelReservation(string $token)
    {
        $reservation = Reservation::where('cancellation_token', $token)->firstOrFail();

        if (in_array($reservation->status, ['cancelled', 'completed', 'seated', 'no_show'])) {
            return back()->with('error', 'Esta reservación no puede cancelarse en su estado actual.');
        }

        $reservation->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Invalidar el token para que el link no pueda volver a cancelar
        $reservation->update(['cancellation_token' => null]);

        return redirect()->route('booking.cancel.done', $reservation->confirmation_code);
    }

    public function cancelDone(string $code)
    {
        $reservation = Reservation::where('confirmation_code', $code)
            ->with('restaurant')
            ->firstOrFail();

        return Inertia::render('Booking/CancelDone', [
            'confirmation_code' => $reservation->confirmation_code,
            'restaurant'        => [
                'name'  => $reservation->restaurant->name,
                'email' => $reservation->restaurant->email,
            ],
        ]);
    }

    public function waitlistForm(Request $request)
    {
        $restaurant = Restaurant::where('is_active', true)->firstOrFail();

        return Inertia::render('Booking/Waitlist', [
            'restaurant' => [
                'name'  => $restaurant->name,
                'email' => $restaurant->email,
            ],
            'prefill' => [
                'date'       => $request->date,
                'party_size' => $request->party_size,
            ],
        ]);
    }

    public function storeWaitlist(StoreWaitlistRequest $request)
    {
        $restaurant = Restaurant::where('is_active', true)->firstOrFail();

        Waitlist::create(array_merge($request->validated(), [
            'restaurant_id' => $restaurant->id,
        ]));

        return redirect()->route('booking.waitlist.success');
    }

    public function waitlistSuccess()
    {
        return Inertia::render('Booking/WaitlistSuccess');
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time);
        return ((int) $h) * 60 + (int) $m;
    }
}
