<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('Profile/Index', [
            'user' => [
                'id'    => $user->id,
                'uuid'  => $user->uuid,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    public function update()
    {
        $user = Auth::user();

        $validated = request()->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return back()->with('success', 'Perfil actualizado exitosamente.');
    }

    public function changePassword()
    {
        $user = Auth::user();

        $validated = request()->validate([
            'current_password'      => ['required', 'current_password'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'current_password.required'      => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'password.required'              => 'La nueva contraseña es requerida.',
            'password.min'                   => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'             => 'Las contraseñas no coinciden.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Cerrar sesión por seguridad después de cambio de contraseña
        Auth::logoutOtherDevices($validated['password']);
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Contraseña actualizada. Por seguridad, iniciá sesión nuevamente.');
    }
}
