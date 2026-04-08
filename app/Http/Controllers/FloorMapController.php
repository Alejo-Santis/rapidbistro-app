<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FloorMapController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $zones = Zone::with(['tables' => fn ($q) => $q->orderBy('number')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($zone) => [
                'id'             => $zone->id,
                'name'           => $zone->name,
                'location'       => $zone->location,
                'location_label' => $zone->location_label,
                'tables'         => $zone->tables->map(fn ($table) => [
                    'id'           => $table->id,
                    'uuid'         => $table->uuid,
                    'number'       => $table->number,
                    'capacity'     => $table->capacity,
                    'min_capacity' => $table->min_capacity ?? 1,
                    'status'       => $table->status,
                    'status_label' => $table->status_label,
                    'reservations' => $table->reservations()
                        ->whereDate('reservation_date', $date)
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->orderBy('starts_at')
                        ->get()
                        ->map(fn ($r) => [
                            'uuid'              => $r->uuid,
                            'confirmation_code' => $r->confirmation_code,
                            'guest_name'        => $r->guest_name,
                            'guest_phone'       => $r->guest_phone,
                            'party_size'        => $r->party_size,
                            'starts_at'         => substr($r->starts_at, 0, 5),
                            'ends_at'           => substr($r->ends_at, 0, 5),
                            'status'            => $r->status,
                            'status_label'      => $r->status_label,
                            'notes'             => $r->notes,
                            'internal_notes'    => $r->internal_notes,
                        ]),
                ]),
            ]);

        $totals = [
            'total'       => $zones->sum(fn ($z) => count($z['tables'])),
            'available'   => $zones->sum(fn ($z) => collect($z['tables'])->where('status', 'available')->count()),
            'reserved'    => $zones->sum(fn ($z) => collect($z['tables'])->where('status', 'reserved')->count()),
            'occupied'    => $zones->sum(fn ($z) => collect($z['tables'])->where('status', 'occupied')->count()),
            'unavailable' => $zones->sum(fn ($z) => collect($z['tables'])->whereIn('status', ['maintenance', 'unavailable'])->count()),
        ];

        return Inertia::render('FloorMap/Index', [
            'zones'        => $zones,
            'selectedDate' => $date,
            'totals'       => $totals,
        ]);
    }
}
