<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token = '';
    private string $phoneNumberId = '';
    private string $apiVersion = 'v19.0';

    public function __construct()
    {
        $this->token         = (string) config('services.whatsapp.token', '');
        $this->phoneNumberId = (string) config('services.whatsapp.phone_number_id', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->token) && ! empty($this->phoneNumberId);
    }

    /**
     * Envía confirmación de reserva (template: reservation_confirmed).
     * El template debe estar aprobado en Meta Business Manager.
     */
    public function sendConfirmation(Reservation $reservation): bool
    {
        if (! $this->isConfigured() || ! $reservation->guest_phone) {
            return false;
        }

        $phone = $this->normalizePhone($reservation->guest_phone);

        return $this->sendTemplate($phone, 'reservation_confirmed', 'es', [
            ['type' => 'text', 'text' => $reservation->guest_name],
            ['type' => 'text', 'text' => $reservation->confirmation_code],
            ['type' => 'text', 'text' => $reservation->reservation_date->format('d/m/Y')],
            ['type' => 'text', 'text' => substr($reservation->starts_at, 0, 5)],
            ['type' => 'text', 'text' => (string) $reservation->party_size],
        ]);
    }

    /**
     * Envía recordatorio (template: reservation_reminder).
     * $type: '24h' | '2h'
     */
    public function sendReminder(Reservation $reservation, string $type = '24h'): bool
    {
        if (! $this->isConfigured() || ! $reservation->guest_phone) {
            return false;
        }

        $phone       = $this->normalizePhone($reservation->guest_phone);
        $timeText    = $type === '24h' ? 'mañana' : 'en 2 horas';

        return $this->sendTemplate($phone, 'reservation_reminder', 'es', [
            ['type' => 'text', 'text' => $reservation->guest_name],
            ['type' => 'text', 'text' => $timeText],
            ['type' => 'text', 'text' => $reservation->reservation_date->format('d/m/Y')],
            ['type' => 'text', 'text' => substr($reservation->starts_at, 0, 5)],
        ]);
    }

    private function sendTemplate(string $to, string $template, string $language, array $components): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'template',
                    'template'          => [
                        'name'       => $template,
                        'language'   => ['code' => $language],
                        'components' => [
                            [
                                'type'       => 'body',
                                'parameters' => $components,
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('WhatsApp API error', [
                    'template' => $template,
                    'to'       => $to,
                    'status'   => $response->status(),
                    'body'     => $response->json(),
                ]);
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('WhatsApp exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Normaliza el número de teléfono a formato E.164 sin '+'.
     * Ej: "+54 11 1234-5678" → "5491112345678"
     */
    private function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Si no tiene código de país, asume el configurado en .env
        $defaultCountry = config('services.whatsapp.default_country_code', '54');
        if (strlen($clean) <= 10) {
            $clean = $defaultCountry . $clean;
        }

        return $clean;
    }
}
