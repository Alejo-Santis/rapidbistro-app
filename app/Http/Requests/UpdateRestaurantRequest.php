<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('restaurant'));
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'settings'                          => ['nullable', 'array'],
            'settings.currency'                 => ['nullable', 'string', 'max:10'],
            'settings.cancellation_policy'      => ['nullable', 'string', 'max:1000'],
            'settings.default_slot_minutes'     => ['nullable', 'integer', 'min:15', 'max:480'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                          => 'El nombre del restaurante es requerido.',
            'email.email'                            => 'El correo electrónico no tiene un formato válido.',
            'settings.default_slot_minutes.min'      => 'La duración mínima de un turno es 15 minutos.',
            'settings.default_slot_minutes.max'      => 'La duración máxima de un turno es 480 minutos.',
        ];
    }
}
