<?php

namespace App\Http\Controllers;

use App\Exports\ReservationsExport;
use App\Models\Reservation;
use App\Models\Restaurant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = Restaurant::firstOrFail();

        $from = $request->date('from', 'Y-m-d') ?? now()->startOfMonth()->toDateString();
        $to   = $request->date('to',   'Y-m-d') ?? now()->toDateString();

        // ── Resumen general del período ──────────────────────────────────────
        $base = Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_date', [$from, $to]);

        $summary = [
            'total'     => (clone $base)->count(),
            'completed' => (clone $base)->whereIn('status', ['completed','seated'])->count(),
            'no_show'   => (clone $base)->where('status', 'no_show')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'covers'    => (clone $base)->whereIn('status', ['confirmed','seated','completed'])->sum('party_size'),
        ];

        // ── Por zona ─────────────────────────────────────────────────────────
        $byZone = DB::table('reservations')
            ->join('tables', 'reservations.table_id', '=', 'tables.id')
            ->join('zones', 'tables.zone_id', '=', 'zones.id')
            ->where('reservations.restaurant_id', $restaurant->id)
            ->whereBetween('reservations.reservation_date', [$from, $to])
            ->selectRaw('zones.name as zone, COUNT(*) as total, SUM(reservations.party_size) as covers,
                SUM(CASE WHEN reservations.status=\'no_show\' THEN 1 ELSE 0 END) as no_shows')
            ->groupBy('zones.name')
            ->orderBy('total', 'desc')
            ->get();

        // ── Por día de la semana ─────────────────────────────────────────────
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $dayLabels = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

        $byDayRaw = Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_date', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("TRIM(TO_CHAR(reservation_date, 'Day')) as dow_name,
                         EXTRACT(ISODOW FROM reservation_date) as dow_num,
                         COUNT(*) as total,
                         SUM(party_size) as covers")
            ->groupByRaw("TRIM(TO_CHAR(reservation_date, 'Day')), EXTRACT(ISODOW FROM reservation_date)")
            ->orderByRaw("EXTRACT(ISODOW FROM reservation_date)")
            ->get()
            ->keyBy(fn ($r) => (int) $r->dow_num);

        $byDayOfWeek = collect(range(1, 7))->map(fn ($n, $i) => [
            'day'    => $dayLabels[$i],
            'total'  => (int) ($byDayRaw[$n]?->total ?? 0),
            'covers' => (int) ($byDayRaw[$n]?->covers ?? 0),
        ])->values();

        // ── Por turno (time_slot name) ───────────────────────────────────────
        $bySlotRaw = Reservation::where('restaurant_id', $restaurant->id)
            ->whereBetween('reservation_date', [$from, $to])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("
                CASE
                    WHEN starts_at::time < '15:00' THEN 'Almuerzo'
                    ELSE 'Cena'
                END as slot,
                COUNT(*) as total,
                SUM(party_size) as covers,
                SUM(CASE WHEN status='no_show' THEN 1 ELSE 0 END) as no_shows
            ")
            ->groupByRaw("CASE WHEN starts_at::time < '15:00' THEN 'Almuerzo' ELSE 'Cena' END")
            ->orderBy('slot')
            ->get();

        // ── Top mesas más usadas ─────────────────────────────────────────────
        $topTables = DB::table('reservations')
            ->join('tables', 'reservations.table_id', '=', 'tables.id')
            ->join('zones', 'tables.zone_id', '=', 'zones.id')
            ->where('reservations.restaurant_id', $restaurant->id)
            ->whereBetween('reservations.reservation_date', [$from, $to])
            ->whereNotIn('reservations.status', ['cancelled'])
            ->selectRaw('tables.number as table_number, zones.name as zone, COUNT(*) as total')
            ->groupBy('tables.number', 'zones.name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Reports/Index', [
            'from'        => $from,
            'to'          => $to,
            'summary'     => $summary,
            'byZone'      => $byZone,
            'byDayOfWeek' => $byDayOfWeek,
            'bySlot'      => $bySlotRaw,
            'topTables'   => $topTables,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $restaurant = Restaurant::firstOrFail();
        $date = $request->date('date', 'Y-m-d') ?? today()->toDateString();

        $reservations = Reservation::with(['table.zone'])
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('reservation_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at')
            ->get()
            ->map(fn ($r) => [
                'confirmation_code' => $r->confirmation_code,
                'guest_name'        => $r->guest_name,
                'guest_phone'       => $r->guest_phone,
                'starts_at'         => substr($r->starts_at, 0, 5),
                'ends_at'           => substr($r->ends_at, 0, 5),
                'party_size'        => $r->party_size,
                'status'            => $r->status_label,
                'table_number'      => $r->table?->number,
                'zone_name'         => $r->table?->zone?->name,
                'notes'             => $r->notes,
            ]);

        $pdf = Pdf::loadView('pdf.reservations-day', [
            'restaurant'   => $restaurant,
            'date'         => \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'),
            'reservations' => $reservations,
            'total'        => $reservations->count(),
            'covers'       => $reservations->sum('party_size'),
        ])->setPaper('a4', 'portrait');

        $filename = 'reservas-' . $date . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $restaurant = Restaurant::firstOrFail();

        $from = $request->input('from') ?? now()->startOfMonth()->toDateString();
        $to   = $request->input('to')   ?? now()->toDateString();

        $filename = "reservas-{$from}-a-{$to}.xlsx";

        return Excel::download(
            new ReservationsExport($restaurant->id, $from, $to),
            $filename
        );
    }
}
