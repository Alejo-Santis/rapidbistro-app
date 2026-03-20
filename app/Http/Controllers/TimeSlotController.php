<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeSlotRequest;
use App\Http\Requests\UpdateTimeSlotRequest;
use App\Models\Restaurant;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TimeSlotController extends Controller
{
    public function index()
    {
        Gate::authorize('update', Restaurant::firstOrFail());

        $restaurant = Restaurant::firstOrFail();

        $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $slots = TimeSlot::where('restaurant_id', $restaurant->id)
            ->get()
            ->sortBy(fn($s) => array_search($s->day_of_week, $daysOrder))
            ->values()
            ->map(fn($s) => [
                'id'                          => $s->id,
                'day_of_week'                 => $s->day_of_week,
                'day_label'                   => $s->day_label,
                'opens_at'                    => substr($s->opens_at, 0, 5),
                'closes_at'                   => substr($s->closes_at, 0, 5),
                'slot_duration_minutes'       => $s->slot_duration_minutes,
                'max_concurrent_reservations' => $s->max_concurrent_reservations,
            ]);

        return Inertia::render('TimeSlots/Index', [
            'slots'      => $slots,
            'daysOfWeek' => [
                ['value' => 'monday',    'label' => 'Lunes'],
                ['value' => 'tuesday',   'label' => 'Martes'],
                ['value' => 'wednesday', 'label' => 'Miércoles'],
                ['value' => 'thursday',  'label' => 'Jueves'],
                ['value' => 'friday',    'label' => 'Viernes'],
                ['value' => 'saturday',  'label' => 'Sábado'],
                ['value' => 'sunday',    'label' => 'Domingo'],
            ],
        ]);
    }

    public function store(StoreTimeSlotRequest $request)
    {
        $restaurant = Restaurant::firstOrFail();

        TimeSlot::create(array_merge($request->validated(), [
            'restaurant_id' => $restaurant->id,
        ]));

        return back()->with('success', 'Horario creado exitosamente.');
    }

    public function update(UpdateTimeSlotRequest $request, TimeSlot $timeSlot)
    {
        $timeSlot->update($request->validated());

        return back()->with('success', 'Horario actualizado exitosamente.');
    }

    public function destroy(TimeSlot $timeSlot)
    {
        Gate::authorize('update', $timeSlot->restaurant);

        $timeSlot->delete();

        return back()->with('success', 'Horario eliminado.');
    }
}
