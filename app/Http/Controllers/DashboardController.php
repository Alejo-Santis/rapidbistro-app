<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::first();

        $today = today();

        $stats = [
            'today_total'     => Reservation::whereDate('reservation_date', $today)->count(),
            'today_pending'   => Reservation::whereDate('reservation_date', $today)->where('status', 'pending')->count(),
            'today_confirmed' => Reservation::whereDate('reservation_date', $today)->where('status', 'confirmed')->count(),
            'today_seated'    => Reservation::whereDate('reservation_date', $today)->where('status', 'seated')->count(),
            'today_cancelled' => Reservation::whereDate('reservation_date', $today)->where('status', 'cancelled')->count(),
            'week_total'      => Reservation::whereBetween('reservation_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'available_tables' => Table::where('status', 'available')->count(),
        ];

        $upcoming = Reservation::with(['table.zone'])
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->orderBy('starts_at')
            ->limit(15)
            ->get()
            ->map(fn($r) => [
                'id'                => $r->id,
                'confirmation_code' => $r->confirmation_code,
                'guest_name'        => $r->guest_name,
                'guest_phone'       => $r->guest_phone,
                'starts_at'         => $r->starts_at,
                'ends_at'           => $r->ends_at,
                'party_size'        => $r->party_size,
                'status'            => $r->status,
                'status_label'      => $r->status_label,
                'table_number'      => $r->table?->number,
                'zone_name'         => $r->table?->zone?->name,
            ]);

        return Inertia::render('Dashboard/Index', [
            'stats'      => $stats,
            'upcoming'   => $upcoming,
            'restaurant' => $restaurant ? ['name' => $restaurant->name] : null,
        ]);
    }
}
