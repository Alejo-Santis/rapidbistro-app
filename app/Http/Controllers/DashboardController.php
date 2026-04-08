<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Waitlist;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::first();
        $today      = today();
        $weekStart  = now()->startOfWeek();
        $weekEnd    = now()->endOfWeek();
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();

        // ── Contadores de hoy ────────────────────────────────────────────────
        $baseToday = Reservation::where('restaurant_id', $restaurant->id)
            ->whereDate('reservation_date', $today);

        $todayStats = [
            'total'     => (clone $baseToday)->count(),
            'pending'   => (clone $baseToday)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseToday)->where('status', 'confirmed')->count(),
            'seated'    => (clone $baseToday)->where('status', 'seated')->count(),
            'completed' => (clone $baseToday)->where('status', 'completed')->count(),
            'no_show'   => (clone $baseToday)->where('status', 'no_show')->count(),
            'cancelled' => (clone $baseToday)->where('status', 'cancelled')->count(),
            'covers'    => (clone $baseToday)->whereIn('status', ['confirmed','seated','completed'])->sum('party_size'),
        ];

        // ── Semana ───────────────────────────────────────────────────────────
        $weekStats = [
            'total'   => Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_date', [$weekStart, $weekEnd])->count(),
            'no_show' => Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_date', [$weekStart, $weekEnd])
                ->where('status', 'no_show')->count(),
            'covers'  => Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_date', [$weekStart, $weekEnd])
                ->whereIn('status', ['confirmed','seated','completed'])->sum('party_size'),
        ];

        // ── Mes ──────────────────────────────────────────────────────────────
        $monthStats = [
            'total'       => Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_date', [$monthStart, $monthEnd])->count(),
            'no_show'     => Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_date', [$monthStart, $monthEnd])
                ->where('status', 'no_show')->count(),
            'covers'      => Reservation::where('restaurant_id', $restaurant->id)
                ->whereBetween('reservation_date', [$monthStart, $monthEnd])
                ->whereIn('status', ['confirmed','seated','completed'])->sum('party_size'),
        ];

        // ── Horas pico (últimos 30 días, conteo por hora de inicio) ─────────
        $hourlyRaw = Reservation::where('restaurant_id', $restaurant->id)
            ->where('reservation_date', '>=', now()->subDays(30))
            ->whereIn('status', ['confirmed','seated','completed','no_show'])
            ->selectRaw("EXTRACT(HOUR FROM starts_at::time) as hour, COUNT(*) as total")
            ->groupByRaw("EXTRACT(HOUR FROM starts_at::time)")
            ->orderByRaw("EXTRACT(HOUR FROM starts_at::time)")
            ->get();

        $peakHours = collect(range(10, 23))->map(fn ($h) => [
            'hour'  => $h,
            'label' => sprintf('%02d:00', $h),
            'total' => (int) ($hourlyRaw->firstWhere('hour', $h)?->total ?? 0),
        ])->values();

        // ── Reservas por día (últimos 14 días) ───────────────────────────────
        $dailyRaw = Reservation::where('restaurant_id', $restaurant->id)
            ->where('reservation_date', '>=', now()->subDays(13))
            ->where('reservation_date', '<=', $today)
            ->selectRaw("reservation_date, COUNT(*) as total, SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END) as no_shows")
            ->groupBy('reservation_date')
            ->orderBy('reservation_date')
            ->get()
            ->keyBy(fn ($r) => $r->reservation_date->format('Y-m-d'));

        $last14Days = collect(range(13, 0))->map(fn ($i) => [
            'date'     => now()->subDays($i)->format('Y-m-d'),
            'label'    => now()->subDays($i)->format('d/m'),
            'total'    => (int) ($dailyRaw[now()->subDays($i)->format('Y-m-d')]?->total ?? 0),
            'no_shows' => (int) ($dailyRaw[now()->subDays($i)->format('Y-m-d')]?->no_shows ?? 0),
        ])->values();

        // ── Ocupación por zona (hoy) ─────────────────────────────────────────
        $zoneOccupancy = DB::table('reservations')
            ->join('tables', 'reservations.table_id', '=', 'tables.id')
            ->join('zones', 'tables.zone_id', '=', 'zones.id')
            ->where('reservations.restaurant_id', $restaurant->id)
            ->whereDate('reservations.reservation_date', $today)
            ->whereIn('reservations.status', ['confirmed','seated','completed'])
            ->selectRaw('zones.name as zone, COUNT(*) as reservations, SUM(reservations.party_size) as covers')
            ->groupBy('zones.name')
            ->orderBy('reservations', 'desc')
            ->get();

        // ── Mesas disponibles ahora ──────────────────────────────────────────
        $zoneIds         = $restaurant->zones()->pluck('id');
        $availableTables = Table::whereIn('zone_id', $zoneIds)->where('status', 'available')->count();
        $totalTables     = Table::whereIn('zone_id', $zoneIds)->count();

        // ── Lista de espera activa ───────────────────────────────────────────
        $waitlistCount = Waitlist::where('restaurant_id', $restaurant->id)
            ->where('status', 'waiting')->count();

        // ── Próximas reservas de hoy ─────────────────────────────────────────
        $upcoming = Reservation::with(['table.zone'])
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'seated'])
            ->orderBy('starts_at')
            ->limit(12)
            ->get()
            ->map(fn ($r) => [
                'uuid'              => $r->uuid,
                'confirmation_code' => $r->confirmation_code,
                'guest_name'        => $r->guest_name,
                'starts_at'         => substr($r->starts_at, 0, 5),
                'party_size'        => $r->party_size,
                'status'            => $r->status,
                'status_label'      => $r->status_label,
                'table_number'      => $r->table?->number,
                'zone_name'         => $r->table?->zone?->name,
            ]);

        return Inertia::render('Dashboard/Index', [
            'restaurant'     => $restaurant ? ['name' => $restaurant->name] : null,
            'todayStats'     => $todayStats,
            'weekStats'      => $weekStats,
            'monthStats'     => $monthStats,
            'peakHours'      => $peakHours,
            'last14Days'     => $last14Days,
            'zoneOccupancy'  => $zoneOccupancy,
            'availableTables'=> $availableTables,
            'totalTables'    => $totalTables,
            'waitlistCount'  => $waitlistCount,
            'upcoming'       => $upcoming,
        ]);
    }
}
