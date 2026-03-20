<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Table::class);
    }

    public function rules(): array
    {
        return [
            'zone_id'      => ['required', 'exists:zones,id'],
            'number'       => ['required', 'string', 'max:50'],
            'capacity'     => ['required', 'integer', 'min:1', 'max:50'],
            'min_capacity' => ['nullable', 'integer', 'min:1', 'lte:capacity'],
            'status'       => ['required', Rule::in(['available', 'reserved', 'occupied', 'maintenance', 'unavailable'])],
        ];
    }

    public function messages(): array
    {
        return [
            'zone_id.required'   => 'Debe seleccionar una zona.',
            'zone_id.exists'     => 'La zona seleccionada no existe.',
            'number.required'    => 'El número de mesa es requerido.',
            'capacity.required'  => 'La capacidad es requerida.',
            'capacity.min'       => 'La capacidad mínima es 1.',
            'capacity.max'       => 'La capacidad máxima es 50.',
            'min_capacity.lte'   => 'La capacidad mínima no puede ser mayor a la capacidad máxima.',
            'status.required'    => 'El estado es requerido.',
            'status.in'          => 'El estado seleccionado no es válido.',
        ];
    }
}
