<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; background: #fff; }

        .header { background: #111827; color: #fff; padding: 24px 32px; margin-bottom: 0; }
        .header h1 { font-size: 20px; font-weight: bold; color: #f59e0b; margin-bottom: 4px; }
        .header p  { font-size: 11px; color: #9ca3af; }

        .meta { padding: 16px 32px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; }
        .meta-item { text-align: center; }
        .meta-item .value { font-size: 22px; font-weight: bold; color: #111827; }
        .meta-item .label { font-size: 10px; color: #6b7280; margin-top: 2px; }

        .content { padding: 20px 32px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #1f2937; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr { border-bottom: 1px solid #e5e7eb; }
        tbody td { padding: 8px 10px; vertical-align: top; }
        .code { font-family: monospace; font-size: 10px; color: #6b7280; }
        .time { font-weight: bold; color: #1f2937; white-space: nowrap; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 9px; font-weight: 600; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-seated    { background: #dbeafe; color: #1e40af; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-completed { background: #f3f4f6; color: #374151; }
        .badge-no_show   { background: #fee2e2; color: #991b1b; }

        .footer { margin-top: 24px; padding: 12px 32px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 9px; display: flex; justify-content: space-between; }
        .notes { font-size: 9px; color: #6b7280; font-style: italic; }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ $restaurant->name }}</h1>
    <p>Reporte de reservas — {{ $date }}</p>
</div>

<div class="meta">
    <div class="meta-item">
        <div class="value">{{ $total }}</div>
        <div class="label">Reservas</div>
    </div>
    <div class="meta-item">
        <div class="value">{{ $covers }}</div>
        <div class="label">Comensales</div>
    </div>
    <div class="meta-item">
        <div class="value">{{ now()->format('H:i') }}</div>
        <div class="label">Generado</div>
    </div>
</div>

<div class="content">
    @if($reservations->isEmpty())
        <p style="text-align:center; color:#6b7280; margin-top:40px;">No hay reservas para esta fecha.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th style="text-align:center">Pax</th>
                    <th>Mesa / Zona</th>
                    <th>Estado</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $r)
                <tr>
                    <td class="code">#{{ $r['confirmation_code'] }}</td>
                    <td class="time">{{ $r['starts_at'] }}–{{ $r['ends_at'] }}</td>
                    <td><strong>{{ $r['guest_name'] }}</strong></td>
                    <td>{{ $r['guest_phone'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $r['party_size'] }}</td>
                    <td>
                        @if($r['table_number'])
                            Mesa {{ $r['table_number'] }}
                            @if($r['zone_name'])<br><span style="color:#6b7280">{{ $r['zone_name'] }}</span>@endif
                        @else
                            <span style="color:#9ca3af">—</span>
                        @endif
                    </td>
                    <td>
                        @php $s = str_replace(' ', '_', strtolower($r['status'])) @endphp
                        <span class="badge badge-{{ $s }}">{{ $r['status'] }}</span>
                    </td>
                    <td class="notes">{{ $r['notes'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="footer">
    <span>{{ $restaurant->name }} · Generado por RapidBistro</span>
    <span>{{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>
