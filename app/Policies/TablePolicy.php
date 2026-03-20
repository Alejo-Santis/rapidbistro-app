<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;

class TablePolicy
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
        return $user->hasPermissionTo('tables.view');
    }

    public function view(User $user, Table $table): bool
    {
        return $user->hasPermissionTo('tables.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tables.create');
    }

    public function update(User $user, Table $table): bool
    {
        return $user->hasPermissionTo('tables.update');
    }

    public function delete(User $user, Table $table): bool
    {
        // No se puede eliminar una mesa con reservaciones activas
        if ($table->reservations()->whereIn('status', ['pending', 'confirmed', 'seated'])->exists()) {
            return false;
        }

        return $user->hasPermissionTo('tables.delete');
    }
}
