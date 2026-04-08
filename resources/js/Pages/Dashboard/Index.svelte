<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'

    let {
        todayStats,
        monthStats,
        peakHours,
        last14Days,
        zoneOccupancy,
        availableTables,
        totalTables,
        waitlistCount,
        upcoming,
    } = $props()

    const statusColors = {
        pending:   'bg-amber-500/10 text-amber-400 border-amber-500/20',
        confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        seated:    'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        completed: 'bg-gray-500/10 text-gray-400 border-gray-500/20',
        no_show:   'bg-red-500/10 text-red-400 border-red-500/20',
    }

    const statusLabels = {
        pending: 'Pendiente', confirmed: 'Confirmada',
        seated: 'En mesa', completed: 'Completada', no_show: 'No show',
    }

    const peakMax  = $derived(Math.max(...peakHours.map(h => h.total), 1))
    const dayMax   = $derived(Math.max(...last14Days.map(d => d.total), 1))

    const noShowRate = $derived(
        monthStats.total > 0 ? Math.round((monthStats.no_show / monthStats.total) * 100) : 0
    )
</script>

<AppLayout title="Dashboard">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-gray-400 text-sm mt-0.5">
                {new Date().toLocaleDateString('es-ES', { weekday:'long', day:'numeric', month:'long', year:'numeric' })}
            </p>
        </div>
        <a
            href="/reports/export-pdf"
            target="_blank"
            class="inline-flex items-center gap-2 px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors border border-gray-700"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            PDF de hoy
        </a>
    </div>

    <!-- KPIs de hoy -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Reservas hoy</p>
            <p class="text-3xl font-bold text-white">{todayStats.total}</p>
            <p class="text-xs text-gray-600 mt-1">{todayStats.covers} comensales</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Confirmadas</p>
            <p class="text-3xl font-bold text-blue-400">{todayStats.confirmed}</p>
            <p class="text-xs text-gray-600 mt-1">{todayStats.seated} en mesa ahora</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Mesas libres</p>
            <p class="text-3xl font-bold text-emerald-400">{availableTables}</p>
            <p class="text-xs text-gray-600 mt-1">de {totalTables} mesas</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Lista espera</p>
            <p class="text-3xl font-bold {waitlistCount > 0 ? 'text-amber-400' : 'text-gray-500'}">{waitlistCount}</p>
            <p class="text-xs text-gray-600 mt-1">en espera ahora</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        <!-- Horas pico -->
        <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-1">Horas pico</p>
            <p class="text-xs text-gray-500 mb-4">Últimos 30 días</p>
            <div class="flex items-end gap-1 h-28">
                {#each peakHours as h}
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div
                            class="w-full rounded-t-sm bg-amber-500/60 group-hover:bg-amber-400 transition-colors"
                            style="height:{Math.max((h.total / peakMax) * 100, 2)}%"
                            title="{h.label}: {h.total} reservas"
                        ></div>
                        <span class="text-[9px] text-gray-600">{h.hour}h</span>
                    </div>
                {/each}
            </div>
        </div>

        <!-- Mes -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Este mes</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-400">Total reservas</span>
                    <span class="text-sm font-bold text-white">{monthStats.total}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-400">Comensales</span>
                    <span class="text-sm font-bold text-white">{monthStats.covers}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-400">No-shows</span>
                    <span class="text-sm font-bold text-red-400">{monthStats.no_show}</span>
                </div>
                <div class="pt-2 border-t border-gray-800">
                    <div class="flex justify-between mb-1">
                        <span class="text-xs text-gray-500">Tasa no-show</span>
                        <span class="text-xs font-semibold {noShowRate > 10 ? 'text-red-400' : 'text-gray-400'}">{noShowRate}%</span>
                    </div>
                    <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full {noShowRate > 10 ? 'bg-red-500' : 'bg-emerald-500'} rounded-full" style="width:{noShowRate}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        <!-- Últimos 14 días -->
        <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Últimos 14 días</p>
            <div class="flex items-end gap-1 h-24">
                {#each last14Days as d}
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        {#if d.no_shows > 0}
                            <div
                                class="w-full rounded-t-sm bg-red-500/60"
                                style="height:{Math.max((d.no_shows / dayMax) * 100, 3)}%; min-height:3px"
                                title="{d.date}: {d.no_shows} no-shows"
                            ></div>
                        {/if}
                        <div
                            class="w-full rounded-t-sm bg-amber-500/60 group-hover:bg-amber-400/80 transition-colors"
                            style="height:{Math.max(((d.total - d.no_shows) / dayMax) * 100, 2)}%"
                            title="{d.date}: {d.total} reservas"
                        ></div>
                        <span class="text-[9px] text-gray-600">{d.label}</span>
                    </div>
                {/each}
            </div>
            <div class="flex gap-4 mt-3 text-xs text-gray-600">
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-amber-500/60 inline-block"></span> Reservas</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-red-500/60 inline-block"></span> No-shows</span>
            </div>
        </div>

        <!-- Zonas -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Zonas hoy</p>
            {#if zoneOccupancy.length === 0}
                <p class="text-xs text-gray-600 text-center mt-8">Sin reservas por zona</p>
            {:else}
                <div class="space-y-3">
                    {#each zoneOccupancy as z}
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-300">{z.zone}</span>
                            <div class="text-right">
                                <span class="text-xs font-bold text-white">{z.reservations}</span>
                                <span class="text-xs text-gray-600 ml-1">res</span>
                                <span class="text-xs text-gray-500 ml-2">{z.covers} pax</span>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <!-- Próximas reservas -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-white">Reservas activas hoy</p>
            <a href="/reservations" class="text-xs text-amber-400 hover:text-amber-300 transition-colors">Ver todas →</a>
        </div>
        {#if upcoming.length === 0}
            <p class="text-xs text-gray-600 text-center py-6">No hay reservas activas para hoy</p>
        {:else}
            <div class="divide-y divide-gray-800">
                {#each upcoming as r}
                    <div class="flex items-center gap-3 py-3">
                        <div class="text-sm font-mono font-bold text-gray-300 w-10 shrink-0">{r.starts_at}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-white truncate">{r.guest_name}</p>
                            <p class="text-xs text-gray-500">
                                {r.party_size} personas
                                {#if r.table_number} · Mesa {r.table_number}{/if}
                                {#if r.zone_name} · {r.zone_name}{/if}
                            </p>
                        </div>
                        <span class="text-[11px] px-2 py-0.5 border rounded-full shrink-0 {statusColors[r.status] ?? 'bg-gray-700 text-gray-400 border-gray-600'}">
                            {statusLabels[r.status] ?? r.status}
                        </span>
                    </div>
                {/each}
            </div>
        {/if}
    </div>

</AppLayout>
