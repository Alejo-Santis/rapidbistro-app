<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\ReservationStatusLog;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Reservation::class);

        $reservations = Reservation::with(['table.zone'])
            ->when($request->date, fn($q) => $q->whereDate('reservation_date', $request->date))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('guest_name', 'ilike', "%{$request->search}%")
                    ->orWhere('guest_email', 'ilike', "%{$request->search}%")
                    ->orWhere('confirmation_code', 'ilike', "%{$request->search}%")
                    ->orWhere('guest_phone', 'ilike', "%{$request->search}%");
            }))
            ->orderBy('reservation_date', 'desc')
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($r) => [
                'id'                => $r->id,
                'uuid'              => $r->uuid,
                'confirmation_code' => $r->confirmation_code,
                'guest_name'        => $r->guest_name,
                'guest_email'       => $r->guest_email,
                'guest_phone'       => $r->guest_phone,
                'reservation_date'  => $r->reservation_date->format('Y-m-d'),
                'starts_at'         => $r->starts_at,
                'ends_at'           => $r->ends_at,
                'party_size'        => $r->party_size,
                'status'            => $r->status,
                'status_label'      => $r->status_label,
                'table_number'      => $r->table?->number,
                'zone_name'         => $r->table?->zone?->name,
                'can_edit'          => Auth::user()->can('update', $r),
                'can_delete'        => Auth::user()->can('delete', $r),
            ]);

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'filters'      => $request->only(['date', 'status', 'search']),
            'can'          => [
                'create' => Auth::user()->can('create', Reservation::class),
            ],
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Reservation::class);

        $tables = Table::with('zone')
            ->orderBy('number')
            ->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'uuid'     => $t->uuid,
                'number'   => $t->number,
                'capacity' => $t->capacity,
                'status'   => $t->status,
                'zone'     => ['id' => $t->zone->id, 'name' => $t->zone->name],
            ]);

        return Inertia::render('Reservations/Create', [
            'tables' => $tables,
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        $restaurant = Restaurant::firstOrFail();

        $reservation = Reservation::create(array_merge($request->validated(), [
            'restaurant_id' => $restaurant->id,
            'created_by'    => Auth::id(),
            'status'        => 'pending',
        ]));

        return redirect()->route('reservations.index')
            ->with('success', "Reservación #{$reservation->confirmation_code} creada exitosamente.");
    }

    public function edit(Reservation $reservation)
    {
        Gate::authorize('update', $reservation);

        $reservation->load(['table.zone', 'statusLogs.user']);

        $tables = Table::with('zone')
            ->orderBy('number')
            ->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'uuid'     => $t->uuid,
                'number'   => $t->number,
                'capacity' => $t->capacity,
                'status'   => $t->status,
                'zone'     => ['id' => $t->zone->id, 'name' => $t->zone->name],
            ]);

        return Inertia::render('Reservations/Edit', [
            'reservation' => [
                'id'                  => $reservation->id,
                'uuid'                => $reservation->uuid,
                'confirmation_code'   => $reservation->confirmation_code,
                'table_id'            => $reservation->table_id,
                'reservation_date'    => $reservation->reservation_date->format('Y-m-d'),
                'starts_at'           => substr($reservation->starts_at, 0, 5),
                'ends_at'             => substr($reservation->ends_at, 0, 5),
                'party_size'          => $reservation->party_size,
                'status'              => $reservation->status,
                'guest_name'          => $reservation->guest_name,
                'guest_email'         => $reservation->guest_email,
                'guest_phone'         => $reservation->guest_phone,
                'notes'               => $reservation->notes,
                'internal_notes'      => $reservation->internal_notes,
                'cancellation_reason' => $reservation->cancellation_reason,
                'status_logs'         => $reservation->statusLogs->map(fn($log) => [
                    'previous_status' => $log->previous_status,
                    'new_status'      => $log->new_status,
                    'reason'          => $log->reason,
                    'user_name'       => $log->user?->name ?? 'Sistema',
                    'created_at'      => $log->created_at->format('d/m/Y H:i'),
                ]),
            ],
            'tables' => $tables,
            'can'    => [
                'cancel' => Auth::user()->can('cancel', $reservation),
                'delete' => Auth::user()->can('delete', $reservation),
            ],
        ]);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $validated = $request->validated();
        $previousStatus = $reservation->status;

        if ($validated['status'] === 'cancelled' && $previousStatus !== 'cancelled') {
            $validated['cancelled_at'] = now();
        }

        $reservation->update($validated);

        if ($previousStatus !== $validated['status']) {
            ReservationStatusLog::create([
                'reservation_id'  => $reservation->id,
                'user_id'         => Auth::id(),
                'previous_status' => $previousStatus,
                'new_status'      => $validated['status'],
                'reason'          => $validated['cancellation_reason'] ?? null,
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'created_at'      => now(),
            ]);
        }

        return redirect()->route('reservations.index')
            ->with('success', 'Reservación actualizada exitosamente.');
    }

    public function destroy(Reservation $reservation)
    {
        Gate::authorize('delete', $reservation);

        $reservation->delete();

        return back()->with('success', 'Reservación eliminada.');
    }
}
