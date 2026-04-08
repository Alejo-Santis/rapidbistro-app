<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = Restaurant::firstOrFail();

        // Perfil CRM creados manualmente
        $profiles = Guest::where('restaurant_id', $restaurant->id)
            ->when($request->search, fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('name',  'ilike', "%{$request->search}%")
                    ->orWhere('email', 'ilike', "%{$request->search}%");
            }))
            ->when($request->vip, fn ($q) => $q->where('is_vip', true))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString()
            ->through(fn ($g) => [
                'id'          => $g->id,
                'uuid'        => $g->uuid,
                'name'        => $g->name,
                'email'       => $g->email,
                'phone'       => $g->phone,
                'is_vip'      => $g->is_vip,
                'allergies'   => $g->allergies,
                'preferences' => $g->preferences,
                'visit_count' => $g->email
                    ? Reservation::where('guest_email', $g->email)
                        ->where('restaurant_id', $restaurant->id)
                        ->whereIn('status', ['completed', 'seated', 'confirmed'])
                        ->count()
                    : 0,
                'no_show_count' => $g->email
                    ? Reservation::where('guest_email', $g->email)
                        ->where('restaurant_id', $restaurant->id)
                        ->where('status', 'no_show')
                        ->count()
                    : 0,
                'last_visit' => $g->email
                    ? Reservation::where('guest_email', $g->email)
                        ->where('restaurant_id', $restaurant->id)
                        ->whereIn('status', ['completed', 'seated'])
                        ->max('reservation_date')
                    : null,
            ]);

        return Inertia::render('Guests/Index', [
            'guests'  => $profiles,
            'filters' => $request->only(['search', 'vip']),
        ]);
    }

    public function show(Guest $guest)
    {
        $restaurant = Restaurant::firstOrFail();

        $reservations = [];
        if ($guest->email) {
            $reservations = Reservation::with(['table.zone'])
                ->where('guest_email', $guest->email)
                ->where('restaurant_id', $restaurant->id)
                ->orderBy('reservation_date', 'desc')
                ->get()
                ->map(fn ($r) => [
                    'uuid'              => $r->uuid,
                    'confirmation_code' => $r->confirmation_code,
                    'reservation_date'  => $r->reservation_date->format('d/m/Y'),
                    'starts_at'         => substr($r->starts_at, 0, 5),
                    'party_size'        => $r->party_size,
                    'status'            => $r->status,
                    'status_label'      => $r->status_label,
                    'table_number'      => $r->table?->number,
                    'zone_name'         => $r->table?->zone?->name,
                    'notes'             => $r->notes,
                ]);
        }

        return Inertia::render('Guests/Show', [
            'guest' => [
                'id'          => $guest->id,
                'uuid'        => $guest->uuid,
                'name'        => $guest->name,
                'email'       => $guest->email,
                'phone'       => $guest->phone,
                'birthday'    => $guest->birthday?->format('Y-m-d'),
                'anniversary' => $guest->anniversary?->format('Y-m-d'),
                'allergies'   => $guest->allergies,
                'preferences' => $guest->preferences,
                'staff_notes' => $guest->staff_notes,
                'is_vip'      => $guest->is_vip,
                'created_at'  => $guest->created_at->format('d/m/Y'),
            ],
            'reservations' => $reservations,
        ]);
    }

    public function store(StoreGuestRequest $request)
    {
        $restaurant = Restaurant::firstOrFail();

        $guest = Guest::create(array_merge($request->validated(), [
            'restaurant_id' => $restaurant->id,
        ]));

        return redirect()->route('guests.show', $guest->uuid)
            ->with('success', 'Perfil de cliente creado.');
    }

    public function update(StoreGuestRequest $request, Guest $guest)
    {
        $guest->update($request->validated());

        return back()->with('success', 'Perfil actualizado.');
    }

    public function destroy(Guest $guest)
    {
        $guest->delete();

        return redirect()->route('guests.index')
            ->with('success', 'Perfil eliminado.');
    }

    // Crea perfil CRM desde una reservación existente
    public function createFromReservation(Request $request)
    {
        $request->validate([
            'guest_name'  => ['required', 'string'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string'],
        ]);

        $restaurant = Restaurant::firstOrFail();

        $guest = Guest::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'email' => $request->guest_email],
            [
                'name'  => $request->guest_name,
                'phone' => $request->guest_phone,
            ]
        );

        return redirect()->route('guests.show', $guest->uuid)
            ->with('success', 'Perfil de cliente listo.');
    }
}
