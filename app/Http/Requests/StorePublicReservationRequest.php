<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at'        => ['required', 'date_format:H:i'],
            'party_size'       => ['required', 'integer', 'min:1', 'max:20'],
            'guest_name'       => ['required', 'string', 'max:255'],
            'guest_email'      => ['required', 'email', 'max:255'],
            'guest_phone'      => ['nullable', 'string', 'max:20'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reservation_date.required'       => 'La fecha de reservación es requerida.',
            'reservation_date.after_or_equal' => 'La fecha debe ser hoy o una fecha futura.',
            'starts_at.required'              => 'Debe seleccionar un horario.',
            'party_size.required'             => 'El número de personas es requerido.',
            'party_size.min'                  => 'Mínimo 1 persona.',
            'party_size.max'                  => 'Máximo 20 personas por reservación.',
            'guest_name.required'             => 'Su nombre es requerido.',
            'guest_email.required'            => 'El correo electrónico es requerido.',
            'guest_email.email'               => 'Ingrese un correo electrónico válido.',
        ];
    }
}
