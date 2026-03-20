<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Zone;

class ZonePolicy
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
        return $user->hasPermissionTo('zones.view');
    }

    public function view(User $user, Zone $zone): bool
    {
        return $user->hasPermissionTo('zones.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('zones.create');
    }

    public function update(User $user, Zone $zone): bool
    {
        return $user->hasPermissionTo('zones.update');
    }

    public function delete(User $user, Zone $zone): bool
    {
        // No se puede eliminar una zona con mesas activas
        if ($zone->tables()->exists()) {
            return false;
        }

        return $user->hasPermissionTo('zones.delete');
    }
}
