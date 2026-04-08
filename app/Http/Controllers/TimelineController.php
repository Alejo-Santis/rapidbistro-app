<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\TimeSlot;
use App\Models\Zone;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $date    = $request->date ?? now()->toDateString();
        $zoneId  = $request->zone_id;

        $dayOfWeek = strtolower(date('l', strtotime($date)));

        $timeSlot = TimeSlot::whereHas('restaurant')
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        $opensAt  = $timeSlot?->opens_at  ?? '12:00:00';
        $closesAt = $timeSlot?->closes_at ?? '23:00:00';

        $openMinutes  = $this->toMinutes($opensAt);
        $closeMinutes = $this->toMinutes($closesAt);
        $totalMinutes = $closeMinutes - $openMinutes;

        // Columnas cada 30 minutos para el header
        $columns = [];
        for ($m = $openMinutes; $m <= $closeMinutes; $m += 30) {
            $columns[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        }

        $tables = Table::with(['zone', 'reservations' => fn ($q) => $q
                ->whereDate('reservation_date', $date)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->orderBy('starts_at'),
            ])
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->whereHas('zone', fn ($q) => $q->where('is_active', true))
            ->orderBy('zone_id')
            ->orderBy('number')
            ->get()
            ->map(fn ($table) => [
                'id'        => $table->id,
                'uuid'      => $table->uuid,
                'number'    => $table->number,
                'capacity'  => $table->capacity,
                'status'    => $table->status,
                'zone_id'   => $table->zone_id,
                'zone_name' => $table->zone?->name,
                'reservations' => $table->reservations->map(fn ($r) => [
                    'uuid'              => $r->uuid,
                    'confirmation_code' => $r->confirmation_code,
                    'guest_name'        => $r->guest_name,
                    'guest_phone'       => $r->guest_phone,
                    'party_size'        => $r->party_size,
                    'starts_at'         => substr($r->starts_at, 0, 5),
                    'ends_at'           => substr($r->ends_at, 0, 5),
                    'status'            => $r->status,
                    'status_label'      => $r->status_label,
                    'internal_notes'    => $r->internal_notes,
                    // Posición relativa en el timeline (0–100%)
                    'left_pct'  => $this->pct($this->toMinutes($r->starts_at) - $openMinutes, $totalMinutes),
                    'width_pct' => $this->pct(
                        $this->toMinutes($r->ends_at) - $this->toMinutes($r->starts_at),
                        $totalMinutes
                    ),
                ]),
            ]);

        $zones = Zone::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('Timeline/Index', [
            'tables'       => $tables,
            'zones'        => $zones,
            'columns'      => $columns,
            'opensAt'      => substr($opensAt, 0, 5),
            'closesAt'     => substr($closesAt, 0, 5),
            'totalMinutes' => $totalMinutes,
            'selectedDate' => $date,
            'selectedZone' => $zoneId,
        ]);
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time);
        return ((int) $h) * 60 + (int) $m;
    }

    private function pct(int $minutes, int $total): float
    {
        if ($total <= 0) return 0;
        return round(max(0, min(100, ($minutes / $total) * 100)), 4);
    }
}
