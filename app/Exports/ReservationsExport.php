<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReservationsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    public function __construct(
        private readonly int    $restaurantId,
        private readonly string $from,
        private readonly string $to,
    ) {}

    public function collection()
    {
        return Reservation::with(['table.zone'])
            ->where('restaurant_id', $this->restaurantId)
            ->whereBetween('reservation_date', [$this->from, $this->to])
            ->orderBy('reservation_date')
            ->orderBy('starts_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Fecha',
            'Hora inicio',
            'Hora fin',
            'Cliente',
            'Email',
            'Teléfono',
            'Personas',
            'Mesa',
            'Zona',
            'Estado',
            'Fuente',
            'Notas',
            'Notas internas',
            'Creada',
        ];
    }

    public function map($r): array
    {
        return [
            $r->confirmation_code,
            $r->reservation_date->format('d/m/Y'),
            substr($r->starts_at, 0, 5),
            substr($r->ends_at, 0, 5),
            $r->guest_name,
            $r->guest_email ?? '',
            $r->guest_phone ?? '',
            $r->party_size,
            $r->table?->number ?? '',
            $r->table?->zone?->name ?? '',
            $r->status_label,
            $r->source ?? 'manual',
            $r->notes ?? '',
            $r->internal_notes ?? '',
            $r->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return "Reservas {$this->from} a {$this->to}";
    }
}
