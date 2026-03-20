<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RestaurantController extends Controller
{
    public function edit()
    {
        $restaurant = Restaurant::firstOrFail();

        Gate::authorize('update', $restaurant);

        return Inertia::render('Restaurant/Settings', [
            'restaurant' => [
                'id'       => $restaurant->id,
                'uuid'     => $restaurant->uuid,
                'name'     => $restaurant->name,
                'slug'     => $restaurant->slug,
                'address'  => $restaurant->address,
                'phone'    => $restaurant->phone,
                'email'    => $restaurant->email,
                'settings' => $restaurant->settings ?? [],
            ],
        ]);
    }

    public function update(UpdateRestaurantRequest $request)
    {
        $restaurant = Restaurant::firstOrFail();

        $restaurant->update($request->validated());

        return back()->with('success', 'Configuración del restaurante actualizada exitosamente.');
    }
}
