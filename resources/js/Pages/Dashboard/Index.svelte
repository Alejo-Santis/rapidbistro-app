<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'

    let { stats, upcoming, restaurant } = $props()

    const statusColors = {
        pending:   'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        seated:    'bg-green-500/10 text-green-400 border-green-500/20',
        completed: 'bg-gray-500/10 text-gray-400 border-gray-500/20',
        cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
        no_show:   'bg-orange-500/10 text-orange-400 border-orange-500/20',
    }

    const statusLabels = {
        pending:   'Pendiente',
        confirmed: 'Confirmada',
        seated:    'En mesa',
        completed: 'Completada',
        cancelled: 'Cancelada',
        no_show:   'No se presentó',
    }
</script>

<AppLayout title="Dashboard">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Dashboard</h1>
        <p class="text-gray-400 text-sm mt-1">
            {restaurant?.name ?? 'RapidBistro'} &mdash; {new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
        </p>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total hoy</p>
            <p class="text-3xl font-bold text-white mt-1">{stats.today_total}</p>
            <p class="text-xs text-gray-500 mt-1">reservaciones</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-yellow-500 font-medium uppercase tracking-wide">Pendientes</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{stats.today_pending}</p>
            <p class="text-xs text-gray-500 mt-1">por confirmar</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-green-500 font-medium uppercase tracking-wide">En mesa</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{stats.today_seated}</p>
            <p class="text-xs text-gray-500 mt-1">en este momento</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-xs text-amber-500 font-medium uppercase tracking-wide">Mesas disp.</p>
            <p class="text-3xl font-bold text-amber-400 mt-1">{stats.available_tables}</p>
            <p class="text-xs text-gray-500 mt-1">disponibles ahora</p>
        </div>
    </div>

    <!-- Semana stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-white">{stats.today_confirmed}</p>
                <p class="text-xs text-gray-500">Confirmadas hoy</p>
            </div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-white">{stats.today_cancelled}</p>
                <p class="text-xs text-gray-500">Canceladas hoy</p>
            </div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <p class="text-xl font-bold text-white">{stats.week_total}</p>
                <p class="text-xs text-gray-500">Total esta semana</p>
            </div>
        </div>
    </div>

    <!-- Tabla de próximas reservaciones -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h2 class="font-semibold text-white">Reservaciones de hoy</h2>
            <a href="/reservations" class="text-sm text-amber-400 hover:text-amber-300 transition-colors">
                Ver todas →
            </a>
        </div>

        {#if upcoming.length === 0}
            <div class="text-center py-12 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm">No hay reservaciones para hoy</p>
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Hora</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Huésped</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Mesa</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Personas</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Estado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        {#each upcoming as r}
                            <tr class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-300 font-mono">
                                    {r.starts_at?.substring(0, 5)} – {r.ends_at?.substring(0, 5)}
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-white font-medium">{r.guest_name}</p>
                                    {#if r.guest_phone}
                                        <p class="text-xs text-gray-500">{r.guest_phone}</p>
                                    {/if}
                                </td>
                                <td class="px-5 py-3.5 text-gray-300">
                                    {#if r.table_number}
                                        Mesa {r.table_number}
                                        {#if r.zone_name}
                                            <span class="text-gray-500">· {r.zone_name}</span>
                                        {/if}
                                    {:else}
                                        <span class="text-gray-600">—</span>
                                    {/if}
                                </td>
                                <td class="px-5 py-3.5 text-gray-300">{r.party_size} pers.</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {statusColors[r.status] ?? ''}">
                                        {statusLabels[r.status] ?? r.status}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <a
                                        href="/reservations/{r.uuid}/edit"
                                        class="text-xs text-amber-400 hover:text-amber-300 transition-colors"
                                    >
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</AppLayout>
