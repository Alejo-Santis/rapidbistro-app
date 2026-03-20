<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with('roles')
            ->when($request->search, fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('name', 'ilike', "%{$request->search}%")
                    ->orWhere('email', 'ilike', "%{$request->search}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($u) => [
                'id'         => $u->id,
                'uuid'       => $u->uuid,
                'name'       => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone,
                'roles'      => $u->roles->pluck('name'),
                'created_at' => $u->created_at->format('d/m/Y'),
            ]);

        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'roles'   => $roles,
            'filters' => $request->only(['search']),
            'can'     => [
                'create' => Auth::user()->can('create', User::class),
                'update' => Auth::user()->hasPermissionTo('users.update'),
                'delete' => Auth::user()->hasPermissionTo('users.delete'),
            ],
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => isset($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
