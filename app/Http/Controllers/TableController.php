<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Table::class);

        $tables = Table::with('zone')
            ->orderBy('number')
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'uuid'         => $t->uuid,
                'number'       => $t->number,
                'capacity'     => $t->capacity,
                'min_capacity' => $t->min_capacity,
                'status'       => $t->status,
                'status_label' => $t->status_label,
                'zone_id'      => $t->zone_id,
                'zone_name'    => $t->zone?->name,
            ]);

        $zones = Zone::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'location']);

        return Inertia::render('Tables/Index', [
            'tables'        => $tables,
            'zones'         => $zones,
            'statusOptions' => [
                ['value' => 'available',   'label' => 'Disponible'],
                ['value' => 'reserved',    'label' => 'Reservada'],
                ['value' => 'occupied',    'label' => 'Ocupada'],
                ['value' => 'maintenance', 'label' => 'Mantenimiento'],
                ['value' => 'unavailable', 'label' => 'No disponible'],
            ],
            'can' => [
                'create' => Auth::user()->can('create', Table::class),
                'update' => Auth::user()->hasPermissionTo('tables.update'),
                'delete' => Auth::user()->hasPermissionTo('tables.delete'),
            ],
        ]);
    }

    public function store(StoreTableRequest $request)
    {
        Table::create($request->validated());

        return back()->with('success', 'Mesa creada exitosamente.');
    }

    public function update(UpdateTableRequest $request, Table $table)
    {
        $table->update($request->validated());

        return back()->with('success', 'Mesa actualizada exitosamente.');
    }

    public function destroy(Table $table)
    {
        Gate::authorize('delete', $table);

        $table->delete();

        return back()->with('success', 'Mesa eliminada.');
    }
}
