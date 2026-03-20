<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'role'     => ['required', 'exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email'    => 'El correo electrónico no tiene un formato válido.',
            'email.unique'   => 'Este correo electrónico ya está en uso.',
            'password.min'   => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required'  => 'Debe asignar un rol al usuario.',
            'role.exists'    => 'El rol seleccionado no existe.',
        ];
    }
}
