<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Reservation::class);
    }

    public function rules(): array
    {
        return [
            'table_id'         => ['required', 'exists:tables,id'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at'        => ['required', 'date_format:H:i'],
            'ends_at'          => ['required', 'date_format:H:i', 'after:starts_at'],
            'party_size'       => ['required', 'integer', 'min:1', 'max:100'],
            'guest_name'       => ['required', 'string', 'max:255'],
            'guest_email'      => ['nullable', 'email', 'max:255'],
            'guest_phone'      => ['nullable', 'string', 'max:20'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'internal_notes'   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'table_id.required'           => 'Debe seleccionar una mesa.',
            'table_id.exists'             => 'La mesa seleccionada no existe.',
            'reservation_date.required'   => 'La fecha de reservación es requerida.',
            'reservation_date.after_or_equal' => 'La fecha debe ser hoy o en el futuro.',
            'starts_at.required'          => 'La hora de inicio es requerida.',
            'starts_at.date_format'       => 'El formato de hora de inicio debe ser HH:MM.',
            'ends_at.required'            => 'La hora de fin es requerida.',
            'ends_at.after'               => 'La hora de fin debe ser posterior a la de inicio.',
            'party_size.required'         => 'El número de personas es requerido.',
            'party_size.min'              => 'Debe haber al menos 1 persona.',
            'party_size.max'              => 'El máximo es 100 personas.',
            'guest_name.required'         => 'El nombre del huésped es requerido.',
        ];
    }
}
