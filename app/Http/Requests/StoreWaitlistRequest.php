<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWaitlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name'     => ['required', 'string', 'max:255'],
            'guest_email'    => ['required', 'email', 'max:255'],
            'guest_phone'    => ['nullable', 'string', 'max:20'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'party_size'     => ['required', 'integer', 'min:1', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_name.required'     => 'Su nombre es requerido.',
            'guest_email.required'    => 'El correo electrónico es requerido.',
            'guest_email.email'       => 'Ingrese un correo electrónico válido.',
            'preferred_date.required' => 'La fecha preferida es requerida.',
            'party_size.required'     => 'El número de personas es requerido.',
        ];
    }
}
