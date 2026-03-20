<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Zone::class);
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'location'    => ['required', Rule::in(['indoor', 'outdoor', 'rooftop', 'bar', 'private', 'lounge'])],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre de la zona es requerido.',
            'location.required' => 'El tipo de área es requerido.',
            'location.in'       => 'El tipo de área seleccionado no es válido.',
        ];
    }
}
