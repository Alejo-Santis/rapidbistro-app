<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Confirmación de reservación</title>
    <style>
        body { margin: 0; padding: 0; background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1e293b; border-radius: 16px; overflow: hidden; }
        .header { background: #f59e0b; padding: 32px; text-align: center; }
        .header h1 { margin: 0; color: #0f172a; font-size: 22px; font-weight: 700; }
        .header p { margin: 4px 0 0; color: #451a03; font-size: 14px; }
        .body { padding: 32px; }
        .greeting { color: #f1f5f9; font-size: 16px; margin: 0 0 24px; }
        .code-box { background: #0f172a; border: 2px solid #f59e0b; border-radius: 12px; padding: 20px; text-align: center; margin: 0 0 24px; }
        .code-label { color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 8px; }
        .code { color: #f59e0b; font-family: monospace; font-size: 28px; font-weight: 700; letter-spacing: 0.15em; margin: 0; }
        .details { background: #0f172a; border-radius: 12px; padding: 20px; margin: 0 0 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid #1e293b; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #64748b; font-size: 13px; }
        .detail-value { color: #f1f5f9; font-size: 13px; font-weight: 500; text-align: right; }
        .policy { color: #64748b; font-size: 12px; line-height: 1.6; margin: 0 0 24px; padding: 16px; background: #0f172a; border-radius: 8px; border-left: 3px solid #f59e0b; }
        .footer { text-align: center; padding: 24px 32px; border-top: 1px solid #334155; }
        .footer p { color: #475569; font-size: 12px; margin: 0; }
        .footer strong { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $reservation->restaurant->name }}</h1>
                <p>Confirmación de reservación</p>
            </div>

            <div class="body">
                <p class="greeting">Hola <strong style="color:#f1f5f9">{{ $reservation->guest_name }}</strong>, tu reservación ha sido confirmada.</p>

                <div class="code-box">
                    <p class="code-label">Código de confirmación</p>
                    <p class="code">{{ $reservation->confirmation_code }}</p>
                </div>

                <div class="details">
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
                    @if($reservation->notes)
                    <div class="detail-row">
                        <span class="detail-label">Notas</span>
                        <span class="detail-value">{{ $reservation->notes }}</span>
                    </div>
                    @endif
                </div>

                @if($reservation->restaurant->settings['cancellation_policy'] ?? null)
                <div class="policy">
                    <strong style="color:#f59e0b">Política de cancelación:</strong><br>
                    {{ $reservation->restaurant->settings['cancellation_policy'] }}
                </div>
                @endif

                <p style="color:#64748b;font-size:13px;margin:0">
                    Si tienes alguna pregunta, contáctanos a
                    <a href="mailto:{{ $reservation->restaurant->email }}" style="color:#f59e0b">{{ $reservation->restaurant->email }}</a>
                    o al {{ $reservation->restaurant->phone }}.
                </p>
            </div>

            <div class="footer">
                <p>© {{ date('Y') }} <strong>{{ $reservation->restaurant->name }}</strong> · {{ $reservation->restaurant->address }}</p>
            </div>
        </div>
    </div>
</body>
</html>
