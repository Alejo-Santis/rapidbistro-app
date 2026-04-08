<?php

namespace App\Http\Controllers;

use App\Mail\WaitlistNotifiedMail;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class WaitlistController extends Controller
{
    public function index(Request $request)
    {
        $waitlist = Waitlist::with('restaurant')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->date,   fn ($q) => $q->whereDate('preferred_date', $request->date))
            ->when($request->search, fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('guest_name',  'ilike', "%{$request->search}%")
                    ->orWhere('guest_email', 'ilike', "%{$request->search}%");
            }))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'notified' THEN 1 WHEN 'booked' THEN 2 ELSE 3 END")
            ->orderBy('preferred_date')
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($w) => [
                'id'             => $w->id,
                'uuid'           => $w->uuid,
                'guest_name'     => $w->guest_name,
                'guest_email'    => $w->guest_email,
                'guest_phone'    => $w->guest_phone,
                'preferred_date' => $w->preferred_date->format('Y-m-d'),
                'preferred_time' => $w->preferred_time ? substr($w->preferred_time, 0, 5) : null,
                'party_size'     => $w->party_size,
                'notes'          => $w->notes,
                'status'         => $w->status,
                'status_label'   => $w->status_label,
                'notified_at'    => $w->notified_at?->format('d/m/Y H:i'),
                'created_at'     => $w->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Waitlist/Index', [
            'waitlist' => $waitlist,
            'filters'  => $request->only(['status', 'date', 'search']),
            'statusOptions' => [
                ['value' => 'pending',  'label' => 'En espera'],
                ['value' => 'notified', 'label' => 'Notificado'],
                ['value' => 'booked',   'label' => 'Reservó'],
                ['value' => 'expired',  'label' => 'Expirado'],
            ],
        ]);
    }

    public function notify(Waitlist $waitlist)
    {
        if ($waitlist->status !== 'pending') {
            return back()->with('error', 'Solo se pueden notificar entradas en estado "En espera".');
        }

        $waitlist->load('restaurant');

        Mail::to($waitlist->guest_email)->send(new WaitlistNotifiedMail($waitlist));

        $waitlist->update([
            'status'      => 'notified',
            'notified_at' => now(),
        ]);

        return back()->with('success', "Se notificó a {$waitlist->guest_name} por email.");
    }

    public function updateStatus(Request $request, Waitlist $waitlist)
    {
        $request->validate([
            'status' => ['required', 'in:pending,notified,booked,expired'],
        ]);

        $waitlist->update(['status' => $request->status]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function destroy(Waitlist $waitlist)
    {
        $waitlist->delete();

        return back()->with('success', 'Registro eliminado de la lista de espera.');
    }
}
