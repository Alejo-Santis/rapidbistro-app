<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('restaurant.update');
    }

    public function rules(): array
    {
        return [
            'day_of_week'                 => ['required', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'name'                        => ['nullable', 'string', 'max:100'],
            'opens_at'                    => ['required', 'date_format:H:i'],
            'closes_at'                   => ['required', 'date_format:H:i', 'after:opens_at'],
            'slot_duration_minutes'       => ['required', 'integer', 'min:15', 'max:480'],
            'max_concurrent_reservations' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week.required'                  => 'El día de la semana es requerido.',
            'day_of_week.in'                        => 'El día seleccionado no es válido.',
            'opens_at.required'                     => 'La hora de apertura es requerida.',
            'closes_at.required'                    => 'La hora de cierre es requerida.',
            'closes_at.after'                       => 'La hora de cierre debe ser posterior a la de apertura.',
            'slot_duration_minutes.required'        => 'La duración del turno es requerida.',
            'max_concurrent_reservations.required'  => 'El número máximo de reservaciones simultáneas es requerido.',
        ];
    }
}
