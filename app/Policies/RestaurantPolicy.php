<?php

namespace App\Policies;

use App\Models\Restaurant;
use App\Models\User;

class RestaurantPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        return $user->hasPermissionTo('restaurant.view');
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->hasPermissionTo('restaurant.update');
    }
}
