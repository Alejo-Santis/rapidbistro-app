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
            'name'                  => 'required|string|max:255',
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'                 => 'nullable|string|max:20',
            'password'              => 'nullable|min:8|confirmed',
            'password_confirmation' => 'nullable',
        ]);

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        return back()->with('success', 'Perfil actualizado exitosamente.');
    }
}
