<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { reservations, filters } = $props()

    let search = $state(filters.search ?? '')
    let statusFilter = $state(filters.status ?? '')
    let dateFilter = $state(filters.date ?? '')

    const statusColors = {
        pending:   'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        seated:    'bg-green-500/10 text-green-400 border-green-500/20',
        completed: 'bg-gray-500/10 text-gray-400 border-gray-500/20',
        cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
        no_show:   'bg-orange-500/10 text-orange-400 border-orange-500/20',
    }

    const statusOptions = [
        { value: '', label: 'Todos los estados' },
        { value: 'pending', label: 'Pendiente' },
        { value: 'confirmed', label: 'Confirmada' },
        { value: 'seated', label: 'En mesa' },
        { value: 'completed', label: 'Completada' },
        { value: 'cancelled', label: 'Cancelada' },
        { value: 'no_show', label: 'No se presentó' },
    ]

    function applyFilters() {
        router.get('/reservations', {
            search: search || undefined,
            status: statusFilter || undefined,
            date: dateFilter || undefined,
        }, { preserveState: true, replace: true })
    }

    function clearFilters() {
        search = ''
        statusFilter = ''
        dateFilter = ''
        router.get('/reservations')
    }

    function deleteReservation(uuid) {
        if (confirm('¿Estás seguro de eliminar esta reservación?')) {
            router.delete(`/reservations/${uuid}`)
        }
    }
</script>

<AppLayout title="Reservaciones">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Reservaciones</h1>
            <p class="text-gray-400 text-sm mt-1">{reservations.meta?.total ?? 0} reservaciones encontradas</p>
        </div>
        <a
            href="/reservations/create"
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nueva reservación
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1">Buscar</label>
            <input
                type="text"
                bind:value={search}
                onkeydown={(e) => e.key === 'Enter' && applyFilters()}
                placeholder="Nombre, email, código..."
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm placeholder-gray-500 focus:outline-none focus:border-amber-500"
            />
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs text-gray-500 mb-1">Estado</label>
            <select
                bind:value={statusFilter}
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
            >
                {#each statusOptions as opt}
                    <option value={opt.value}>{opt.label}</option>
                {/each}
            </select>
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs text-gray-500 mb-1">Fecha</label>
            <input
                type="date"
                bind:value={dateFilter}
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
            />
        </div>
        <div class="flex gap-2">
            <button
                onclick={applyFilters}
                class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-medium rounded-lg text-sm transition-colors"
            >
                Filtrar
            </button>
            <button
                onclick={clearFilters}
                class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors"
            >
                Limpiar
            </button>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        {#if reservations.data.length === 0}
            <div class="text-center py-16 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-sm">No se encontraron reservaciones</p>
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Código</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Huésped</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Fecha</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Horario</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Mesa</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Pers.</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Estado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        {#each reservations.data as r}
                            <tr class="hover:bg-gray-800/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-xs text-amber-400">{r.confirmation_code}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-white font-medium">{r.guest_name}</p>
                                    {#if r.guest_email}
                                        <p class="text-xs text-gray-500">{r.guest_email}</p>
                                    {/if}
                                </td>
                                <td class="px-5 py-3.5 text-gray-300 whitespace-nowrap">
                                    {new Date(r.reservation_date + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })}
                                </td>
                                <td class="px-5 py-3.5 text-gray-300 font-mono text-xs whitespace-nowrap">
                                    {r.starts_at?.substring(0, 5)} – {r.ends_at?.substring(0, 5)}
                                </td>
                                <td class="px-5 py-3.5 text-gray-300">
                                    {#if r.table_number}
                                        Mesa {r.table_number}
                                        {#if r.zone_name}
                                            <br/><span class="text-xs text-gray-500">{r.zone_name}</span>
                                        {/if}
                                    {:else}
                                        <span class="text-gray-600">—</span>
                                    {/if}
                                </td>
                                <td class="px-5 py-3.5 text-gray-300">{r.party_size}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {statusColors[r.status] ?? ''}">
                                        {r.status_label}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <a
                                            href="/reservations/{r.uuid}/edit"
                                            class="text-xs text-amber-400 hover:text-amber-300 transition-colors"
                                        >
                                            Editar
                                        </a>
                                        <button
                                            onclick={() => deleteReservation(r.uuid)}
                                            class="text-xs text-red-400 hover:text-red-300 transition-colors"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            {#if reservations.meta?.last_page > 1}
                <div class="flex items-center justify-between px-5 py-4 border-t border-gray-800">
                    <p class="text-xs text-gray-500">
                        Mostrando {reservations.meta.from}–{reservations.meta.to} de {reservations.meta.total}
                    </p>
                    <div class="flex gap-1">
                        {#each reservations.meta.links as link}
                            {#if link.url}
                                <a
                                    href={link.url}
                                    class="px-3 py-1.5 text-xs rounded-lg transition-colors
                                        {link.active
                                            ? 'bg-amber-500 text-gray-900 font-semibold'
                                            : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white'}"
                                >
                                    {@html link.label}
                                </a>
                            {:else}
                                <span class="px-3 py-1.5 text-xs text-gray-600">{@html link.label}</span>
                            {/if}
                        {/each}
                    </div>
                </div>
            {/if}
        {/if}
    </div>
</AppLayout>
