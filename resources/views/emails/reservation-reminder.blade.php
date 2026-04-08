<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recordatorio de reservación</title>
    <style>
        body { margin: 0; padding: 0; background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1e293b; border-radius: 16px; overflow: hidden; }
        .header { background: #1d4ed8; padding: 32px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 4px 0 0; color: #bfdbfe; font-size: 14px; }
        .body { padding: 32px; }
        .greeting { color: #f1f5f9; font-size: 16px; margin: 0 0 24px; }
        .when-badge { display: inline-block; background: #1d4ed8; color: #fff; font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 99px; margin: 0 0 24px; }
        .details { background: #0f172a; border-radius: 12px; padding: 20px; margin: 0 0 24px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #1e293b; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #64748b; font-size: 13px; }
        .detail-value { color: #f1f5f9; font-size: 13px; font-weight: 500; }
        .code { color: #f59e0b; font-family: monospace; font-weight: 700; }
        .footer { text-align: center; padding: 24px 32px; border-top: 1px solid #334155; }
        .footer p { color: #475569; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $reservation->restaurant->name }}</h1>
                <p>Recordatorio de reservación</p>
            </div>

            <div class="body">
                <p class="greeting">Hola <strong style="color:#f1f5f9">{{ $reservation->guest_name }}</strong>, te recordamos que tienes una reservación próximamente.</p>

                <span class="when-badge">
                    @if($type === '24h') Tu reservación es mañana @else Tu reservación es en 2 horas @endif
                </span>

                <div class="details">
                    <div class="detail-row">
                        <span class="detail-label">Código</span>
                        <span class="detail-value code">{{ $reservation->confirmation_code }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha</span>
                        <span class="detail-value">{{ $reservation->reservation_date->format('d \d\e F \d\e Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Horario</span>
                        <span class="detail-value">{{ substr($reservation->starts_at, 0, 5) }} – {{ substr($reservation->ends_at, 0, 5) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Personas</span>
                        <span class="detail-value">{{ $reservation->party_size }}</span>
                    </div>
                    @if($reservation->table)
                    <div class="detail-row">
                        <span class="detail-label">Mesa</span>
                        <span class="detail-value">{{ $reservation->table->number }} — {{ $reservation->table->zone->name }}</span>
                    </div>
                    @endif
                </div>

                <p style="color:#64748b;font-size:13px;margin:0">
                    ¿Necesitas cancelar o modificar? Contáctanos a
                    <a href="mailto:{{ $reservation->restaurant->email }}" style="color:#f59e0b">{{ $reservation->restaurant->email }}</a>.
                </p>
            </div>

            <div class="footer">
                <p>© {{ date('Y') }} {{ $reservation->restaurant->name }} · {{ $reservation->restaurant->address }}</p>
            </div>
        </div>
    </div>
</body>
</html>
