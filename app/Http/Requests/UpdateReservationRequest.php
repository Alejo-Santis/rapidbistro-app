<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('reservation'));
    }

    public function rules(): array
    {
        return [
            'table_id'            => ['required', 'exists:tables,id'],
            'reservation_date'    => ['required', 'date'],
            'starts_at'           => ['required', 'date_format:H:i'],
            'ends_at'             => ['required', 'date_format:H:i'],
            'party_size'          => ['required', 'integer', 'min:1', 'max:100'],
            'guest_name'          => ['required', 'string', 'max:255'],
            'guest_email'         => ['nullable', 'email', 'max:255'],
            'guest_phone'         => ['nullable', 'string', 'max:20'],
            'notes'               => ['nullable', 'string', 'max:1000'],
            'internal_notes'      => ['nullable', 'string', 'max:1000'],
            'status'              => ['required', Rule::in(['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])],
            'cancellation_reason' => ['nullable', 'string', 'max:500', 'required_if:status,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'table_id.required'                   => 'Debe seleccionar una mesa.',
            'reservation_date.required'            => 'La fecha de reservación es requerida.',
            'starts_at.required'                   => 'La hora de inicio es requerida.',
            'ends_at.required'                     => 'La hora de fin es requerida.',
            'party_size.required'                  => 'El número de personas es requerido.',
            'guest_name.required'                  => 'El nombre del huésped es requerido.',
            'status.required'                      => 'El estado es requerido.',
            'status.in'                            => 'El estado seleccionado no es válido.',
            'cancellation_reason.required_if'      => 'Debe indicar el motivo de la cancelación.',
        ];
    }
}
