<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Disponibilidad en lista de espera</title>
    <style>
        body { margin: 0; padding: 0; background: #0f172a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #1e293b; border-radius: 16px; overflow: hidden; }
        .header { background: #16a34a; padding: 32px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; font-weight: 700; }
        .header p { margin: 4px 0 0; color: #bbf7d0; font-size: 14px; }
        .body { padding: 32px; }
        .greeting { color: #f1f5f9; font-size: 16px; margin: 0 0 24px; }
        .highlight { background: #0f172a; border: 2px solid #16a34a; border-radius: 12px; padding: 20px; text-align: center; margin: 0 0 24px; }
        .highlight p { margin: 0; color: #4ade80; font-size: 18px; font-weight: 700; }
        .highlight small { color: #94a3b8; font-size: 13px; }
        .details { background: #0f172a; border-radius: 12px; padding: 20px; margin: 0 0 24px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #1e293b; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #64748b; font-size: 13px; }
        .detail-value { color: #f1f5f9; font-size: 13px; font-weight: 500; }
        .cta { display: block; background: #f59e0b; color: #0f172a; text-decoration: none; font-weight: 700; font-size: 15px; text-align: center; padding: 14px 24px; border-radius: 12px; margin: 0 0 24px; }
        .note { color: #64748b; font-size: 12px; line-height: 1.6; }
        .footer { text-align: center; padding: 24px 32px; border-top: 1px solid #334155; }
        .footer p { color: #475569; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $waitlist->restaurant->name }}</h1>
                <p>¡Tenemos disponibilidad para ti!</p>
            </div>

            <div class="body">
                <p class="greeting">Hola <strong style="color:#f1f5f9">{{ $waitlist->guest_name }}</strong>, estabas en nuestra lista de espera y ahora tenemos disponibilidad.</p>

                <div class="highlight">
                    <p>¡Mesa disponible!</p>
                    <small>Para {{ $waitlist->party_size }} persona{{ $waitlist->party_size > 1 ? 's' : '' }} el {{ $waitlist->preferred_date->format('d \d\e F \d\e Y') }}</small>
                </div>

                <div class="details">
                    <div class="detail-row">
                        <span class="detail-label">Fecha preferida</span>
                        <span class="detail-value">{{ $waitlist->preferred_date->format('d/m/Y') }}</span>
                    </div>
                    @if($waitlist->preferred_time)
                    <div class="detail-row">
                        <span class="detail-label">Hora preferida</span>
                        <span class="detail-value">{{ substr($waitlist->preferred_time, 0, 5) }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Personas</span>
                        <span class="detail-value">{{ $waitlist->party_size }}</span>
                    </div>
                </div>

                <a href="{{ url('/reservar') }}" class="cta">Reservar ahora →</a>

                <p class="note">
                    Esta disponibilidad es limitada. Te recomendamos reservar pronto.<br><br>
                    Si ya no necesitas la reservación, puedes ignorar este mensaje.
                </p>
            </div>

            <div class="footer">
                <p>© {{ date('Y') }} <strong>{{ $waitlist->restaurant->name }}</strong> · {{ $waitlist->restaurant->address }}</p>
            </div>
        </div>
    </div>
</body>
</html>
