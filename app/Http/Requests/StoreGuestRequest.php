<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $restaurantId = $this->route('guest')?->restaurant_id
            ?? \App\Models\Restaurant::value('id');

        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255', Rule::unique('guests')->where('restaurant_id', $restaurantId)->ignore($this->route('guest'))],
            'phone'       => ['nullable', 'string', 'max:20'],
            'birthday'    => ['nullable', 'date'],
            'anniversary' => ['nullable', 'date'],
            'allergies'   => ['nullable', 'string', 'max:1000'],
            'preferences' => ['nullable', 'string', 'max:1000'],
            'staff_notes' => ['nullable', 'string', 'max:1000'],
            'is_vip'      => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre del cliente es requerido.',
            'email.unique'   => 'Ya existe un perfil con ese correo electrónico.',
        ];
    }
}
