<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\TimeSlot;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $restaurant = Restaurant::where('is_active', true)->first();

        $schedule = [];

        if ($restaurant) {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $dayLabels = [
                'monday'    => 'Lunes',
                'tuesday'   => 'Martes',
                'wednesday' => 'Miércoles',
                'thursday'  => 'Jueves',
                'friday'    => 'Viernes',
                'saturday'  => 'Sábado',
                'sunday'    => 'Domingo',
            ];

            $slots = TimeSlot::where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->orderByRaw("ARRAY_POSITION(ARRAY['monday','tuesday','wednesday','thursday','friday','saturday','sunday'], day_of_week)")
                ->orderBy('opens_at')
                ->get();

            foreach ($days as $day) {
                $daySlots = $slots->where('day_of_week', $day)->values();
                if ($daySlots->isEmpty()) continue;

                $schedule[] = [
                    'day'   => $dayLabels[$day],
                    'slots' => $daySlots->map(fn ($s) => [
                        'name'       => $s->name,
                        'opens_at'   => substr($s->opens_at, 0, 5),
                        'closes_at'  => substr($s->closes_at, 0, 5),
                    ])->all(),
                ];
            }
        }

        return Inertia::render('Landing/Index', [
            'restaurant' => $restaurant ? [
                'name'    => $restaurant->name,
                'address' => $restaurant->address,
                'phone'   => $restaurant->phone,
                'email'   => $restaurant->email,
                'policy'  => $restaurant->settings['cancellation_policy'] ?? null,
            ] : null,
            'schedule' => $schedule,
        ]);
    }
}
