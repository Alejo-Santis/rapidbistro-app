<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private string $calendarId;
    private string $serviceAccountJson;

    public function __construct()
    {
        $this->calendarId         = config('services.google_calendar.calendar_id') ?? '';
        $this->serviceAccountJson = config('services.google_calendar.service_account_json') ?? '';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->calendarId) && ! empty($this->serviceAccountJson);
    }

    /**
     * Crea o actualiza un evento en Google Calendar para una reserva confirmada.
     * Usa la Google Calendar API v3 con Service Account (JWT auth).
     */
    public function upsertEvent(Reservation $reservation): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $token     = $this->getAccessToken();
            $eventData = $this->buildEventData($reservation);

            // Si ya tiene google_event_id, actualiza; si no, crea
            if ($reservation->google_event_id) {
                $response = Http::withToken($token)
                    ->put(
                        "https://www.googleapis.com/calendar/v3/calendars/{$this->calendarId}/events/{$reservation->google_event_id}",
                        $eventData
                    );
            } else {
                $response = Http::withToken($token)
                    ->post(
                        "https://www.googleapis.com/calendar/v3/calendars/{$this->calendarId}/events",
                        $eventData
                    );
            }

            if ($response->failed()) {
                Log::error('Google Calendar API error', [
                    'reservation' => $reservation->uuid,
                    'status'      => $response->status(),
                    'body'        => $response->json(),
                ]);
                return null;
            }

            $eventId = $response->json('id');

            // Guarda el event ID en la reserva
            $reservation->updateQuietly(['google_event_id' => $eventId]);

            return $eventId;

        } catch (\Throwable $e) {
            Log::error('Google Calendar exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteEvent(Reservation $reservation): bool
    {
        if (! $this->isConfigured() || ! $reservation->google_event_id) {
            return false;
        }

        try {
            $token    = $this->getAccessToken();
            $response = Http::withToken($token)
                ->delete(
                    "https://www.googleapis.com/calendar/v3/calendars/{$this->calendarId}/events/{$reservation->google_event_id}"
                );

            if ($response->successful()) {
                $reservation->updateQuietly(['google_event_id' => null]);
                return true;
            }

            return false;

        } catch (\Throwable $e) {
            Log::error('Google Calendar delete exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    private function buildEventData(Reservation $reservation): array
    {
        $date  = $reservation->reservation_date->format('Y-m-d');
        $start = $date . 'T' . substr($reservation->starts_at, 0, 5) . ':00';
        $end   = $date . 'T' . substr($reservation->ends_at, 0, 5)   . ':00';

        $description = implode("\n", array_filter([
            "Código: {$reservation->confirmation_code}",
            "Personas: {$reservation->party_size}",
            $reservation->guest_phone ? "Tel: {$reservation->guest_phone}" : null,
            $reservation->guest_email ? "Email: {$reservation->guest_email}" : null,
            $reservation->table ? "Mesa: {$reservation->table->number}" : null,
            $reservation->notes ? "Notas: {$reservation->notes}" : null,
        ]));

        return [
            'summary'     => "Reserva #{$reservation->confirmation_code} — {$reservation->guest_name} ({$reservation->party_size} pax)",
            'description' => $description,
            'start'       => ['dateTime' => $start, 'timeZone' => config('app.timezone', 'America/Bogota')],
            'end'         => ['dateTime' => $end,   'timeZone' => config('app.timezone', 'America/Bogota')],
            'colorId'     => $this->colorForStatus($reservation->status),
            'status'      => $reservation->status === 'cancelled' ? 'cancelled' : 'confirmed',
        ];
    }

    /**
     * Obtiene un access token con JWT (Service Account).
     * Soporta tanto JSON string como path a archivo.
     */
    private function getAccessToken(): string
    {
        $sa = json_decode(
            file_exists($this->serviceAccountJson)
                ? file_get_contents($this->serviceAccountJson)
                : $this->serviceAccountJson,
            true
        );

        $now    = time();
        $expiry = $now + 3600;

        $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $expiry,
        ]));

        $signingInput = "{$header}.{$payload}";
        openssl_sign($signingInput, $signature, $sa['private_key'], 'sha256WithRSAEncryption');
        $jwt = $signingInput . '.' . base64url_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        return $response->json('access_token');
    }

    private function colorForStatus(string $status): string
    {
        return match ($status) {
            'confirmed' => '9',  // blueberry
            'seated'    => '10', // sage
            'completed' => '8',  // graphite
            'no_show'   => '11', // tomato
            'cancelled' => '4',  // flamingo
            default     => '5',  // banana
        };
    }
}

if (! function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
