<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $model): bool
    {
        // No puede editarse a sí mismo para cambiar su propio rol
        return $user->hasPermissionTo('users.update');
    }

    public function delete(User $user, User $model): bool
    {
        // No puede eliminar su propia cuenta
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('users.delete');
    }
}
