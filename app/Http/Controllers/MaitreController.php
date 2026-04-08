<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Waitlist;
use Inertia\Inertia;

class MaitreController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::firstOrFail();
        $today      = today();
        $now        = now()->format('H:i');

        $reservations = Reservation::with(['table.zone'])
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('reservation_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($r) => [
                'uuid'         => $r->uuid,
                'guest_name'   => $r->guest_name,
                'guest_phone'  => $r->guest_phone,
                'starts_at'    => substr($r->starts_at, 0, 5),
                'ends_at'      => substr($r->ends_at, 0, 5),
                'party_size'   => $r->party_size,
                'status'       => $r->status,
                'status_label' => $r->status_label,
                'table_number' => $r->table?->number,
                'zone_name'    => $r->table?->zone?->name,
                'notes'        => $r->notes,
                'internal_notes' => $r->internal_notes,
                'is_past'      => $r->starts_at < $now,
                'is_soon'      => $r->starts_at >= $now && $r->starts_at <= now()->addMinutes(30)->format('H:i'),
            ]);

        $waitingWalkIns = Waitlist::where('restaurant_id', $restaurant->id)
            ->where('status', 'waiting')
            ->where('source', 'walk_in')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($w) => [
                'uuid'       => $w->uuid,
                'name'       => $w->name,
                'party_size' => $w->party_size,
                'notes'      => $w->notes,
                'wait_min'   => (int) now()->diffInMinutes($w->created_at),
            ]);

        $stats = [
            'total'     => $reservations->count(),
            'pending'   => $reservations->where('status', 'pending')->count(),
            'confirmed' => $reservations->where('status', 'confirmed')->count(),
            'seated'    => $reservations->where('status', 'seated')->count(),
            'completed' => $reservations->where('status', 'completed')->count(),
            'no_show'   => $reservations->where('status', 'no_show')->count(),
            'walk_ins'  => $waitingWalkIns->count(),
            'covers'    => $reservations->whereIn('status', ['confirmed','seated','completed'])->sum('party_size'),
        ];

        $zoneIds    = $restaurant->zones()->pluck('id');
        $freeTables = Table::whereIn('zone_id', $zoneIds)
            ->where('status', 'available')
            ->count();

        return Inertia::render('Maitre/Index', [
            'reservations'   => $reservations->values(),
            'waitingWalkIns' => $waitingWalkIns,
            'stats'          => $stats,
            'freeTables'     => $freeTables,
            'now'            => $now,
        ]);
    }
}
