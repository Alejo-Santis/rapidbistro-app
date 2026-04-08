<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialDateRequest;
use App\Models\Restaurant;
use App\Models\SpecialDate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SpecialDateController extends Controller
{
    public function index(Request $request)
    {
        $restaurant = Restaurant::firstOrFail();

        $year  = $request->year  ?? now()->year;
        $month = $request->month ?? now()->month;

        $mapDate = fn ($d) => [
            'id'                => $d->id,
            'name'              => $d->name,
            'date'              => $d->date->format('Y-m-d'),
            'type'              => $d->type,
            'type_label'        => $d->type_label,
            'description'       => $d->description,
            'capacity_override' => $d->capacity_override,
            'booking_allowed'   => (bool) $d->booking_allowed,
            'color'             => $d->color,
        ];

        // Agrupado por mes (todos los registros del restaurante)
        $allDates = SpecialDate::where('restaurant_id', $restaurant->id)
            ->orderBy('date', 'desc')
            ->get();

        $byMonth = $allDates
            ->groupBy(fn ($d) => $d->date->format('Y-m'))
            ->map(fn ($group) => $group->map($mapDate)->values())
            ->toArray();

        // Próximas fechas (desde hoy)
        $upcoming = SpecialDate::where('restaurant_id', $restaurant->id)
            ->where('date', '>=', today())
            ->orderBy('date')
            ->limit(20)
            ->get()
            ->map($mapDate)
            ->values();

        return Inertia::render('SpecialDates/Index', [
            'upcoming' => $upcoming,
            'byMonth'  => $byMonth,
        ]);
    }

    public function store(StoreSpecialDateRequest $request)
    {
        $restaurant = Restaurant::firstOrFail();

        SpecialDate::create(array_merge($request->validated(), [
            'restaurant_id' => $restaurant->id,
        ]));

        return back()->with('success', 'Fecha especial creada.');
    }

    public function update(StoreSpecialDateRequest $request, SpecialDate $specialDate)
    {
        $specialDate->update($request->validated());

        return back()->with('success', 'Fecha especial actualizada.');
    }

    public function destroy(SpecialDate $specialDate)
    {
        $specialDate->delete();

        return back()->with('success', 'Fecha eliminada.');
    }
}
