<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpecialDateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'date'              => ['required', 'date'],
            'type'              => ['required', Rule::in(['event', 'blocked', 'limited'])],
            'description'       => ['nullable', 'string', 'max:1000'],
            'capacity_override' => ['nullable', 'integer', 'min:1', 'max:500'],
            'booking_allowed'   => ['boolean'],
            'color'             => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del evento es requerido.',
            'date.required' => 'La fecha es requerida.',
            'type.required' => 'El tipo es requerido.',
            'color.regex'   => 'El color debe ser un código hex válido (ej: #f59e0b).',
        ];
    }
}
