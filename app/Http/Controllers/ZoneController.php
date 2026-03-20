<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\Restaurant;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ZoneController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Zone::class);

        $restaurant = Restaurant::firstOrFail();

        $zones = Zone::with(['tables'])
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($z) => [
                'id'             => $z->id,
                'uuid'           => $z->uuid,
                'name'           => $z->name,
                'description'    => $z->description,
                'location'       => $z->location,
                'location_label' => $z->location_label,
                'sort_order'     => $z->sort_order,
                'is_active'      => $z->is_active,
                'tables_count'   => $z->tables->count(),
            ]);

        return Inertia::render('Zones/Index', [
            'zones'           => $zones,
            'locationOptions' => [
                ['value' => 'indoor',  'label' => 'Interior'],
                ['value' => 'outdoor', 'label' => 'Exterior'],
                ['value' => 'rooftop', 'label' => 'Terraza'],
                ['value' => 'bar',     'label' => 'Bar'],
                ['value' => 'private', 'label' => 'Privado'],
                ['value' => 'lounge',  'label' => 'Lounge'],
            ],
            'can' => [
                'create' => Auth::user()->can('create', Zone::class),
                'update' => Auth::user()->hasPermissionTo('zones.update'),
                'delete' => Auth::user()->hasPermissionTo('zones.delete'),
            ],
        ]);
    }

    public function store(StoreZoneRequest $request)
    {
        $restaurant = Restaurant::firstOrFail();

        Zone::create(array_merge($request->validated(), [
            'restaurant_id' => $restaurant->id,
        ]));

        return back()->with('success', 'Zona creada exitosamente.');
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());

        return back()->with('success', 'Zona actualizada exitosamente.');
    }

    public function destroy(Zone $zone)
    {
        Gate::authorize('delete', $zone);

        $zone->delete();

        return back()->with('success', 'Zona eliminada.');
    }
}
