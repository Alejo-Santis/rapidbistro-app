<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Super-admin puede todo sin restricciones.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('reservations.view');
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('reservations.create');
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.update');
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        // Solo se puede cancelar si no está ya cancelada o completada
        if (in_array($reservation->status, ['cancelled', 'completed', 'no_show'])) {
            return false;
        }

        return $user->hasPermissionTo('reservations.cancel');
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->hasPermissionTo('reservations.delete');
    }
}
