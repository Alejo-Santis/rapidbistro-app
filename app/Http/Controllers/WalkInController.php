<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalkInController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::firstOrFail();

        $entries = Waitlist::where('restaurant_id', $restaurant->id)
            ->where('source', 'walk_in')
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['booked', 'expired'])
            ->orderBy('arrived_at')
            ->get()
            ->map(fn ($w) => [
                'id'         => $w->id,
                'uuid'       => $w->uuid,
                'guest_name' => $w->guest_name,
                'guest_phone'=> $w->guest_phone,
                'party_size' => $w->party_size,
                'notes'      => $w->notes,
                'status'     => $w->status,
                'arrived_at' => ($w->arrived_at ?? $w->created_at)?->toISOString(),
            ]);

        return Inertia::render('WalkIn/Index', [
            'entries' => $entries,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone'=> ['nullable', 'string', 'max:20'],
            'party_size' => ['required', 'integer', 'min:1', 'max:20'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $restaurant = Restaurant::firstOrFail();

        Waitlist::create([
            'restaurant_id'  => $restaurant->id,
            'source'         => 'walk_in',
            'guest_name'     => $request->guest_name,
            'guest_email'    => null,
            'guest_phone'    => $request->guest_phone,
            'party_size'     => $request->party_size,
            'preferred_date' => today(),
            'notes'          => $request->notes,
            'status'         => 'pending',
            'arrived_at'     => now(),
        ]);

        return back()->with('success', "{$request->guest_name} agregado a la fila.");
    }

    public function seat(Waitlist $waitlist)
    {
        $waitlist->update(['status' => 'booked']);

        return back()->with('success', "{$waitlist->guest_name} fue sentado.");
    }

    public function remove(Waitlist $waitlist)
    {
        $waitlist->update(['status' => 'expired']);

        return back()->with('success', 'Removido de la fila.');
    }
}
