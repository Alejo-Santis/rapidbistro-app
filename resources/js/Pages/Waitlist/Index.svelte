<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import Pagination from '../../Components/Pagination.svelte'
    import { router } from '@inertiajs/svelte'
    import { untrack } from 'svelte'

    let { waitlist, filters, statusOptions } = $props()

    let search       = $state(untrack(() => filters.search ?? ''))
    let statusFilter = $state(untrack(() => filters.status ?? ''))
    let dateFilter   = $state(untrack(() => filters.date ?? ''))

    const STATUS_CFG = {
        pending:  { bg: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',  dot: 'bg-yellow-400'  },
        notified: { bg: 'bg-blue-500/10 text-blue-400 border-blue-500/20',        dot: 'bg-blue-400'    },
        booked:   { bg: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20', dot: 'bg-emerald-400' },
        expired:  { bg: 'bg-gray-500/10 text-gray-500 border-gray-700',           dot: 'bg-gray-600'    },
    }

    const cfg = (status) => STATUS_CFG[status] ?? STATUS_CFG.pending

    const applyFilters = () => {
        router.get('/waitlist', {
            search: search || undefined,
            status: statusFilter || undefined,
            date:   dateFilter || undefined,
        }, { preserveState: true, replace: true })
    }

    const clearFilters = () => {
        search = ''
        statusFilter = ''
        dateFilter = ''
        router.get('/waitlist')
    }

    const notify = (entry) => {
        if (!confirm(`¿Notificar a "${entry.guest_name}" por email que hay disponibilidad?`)) return
        router.patch(`/waitlist/${entry.id}/notify`)
    }

    const markBooked = (entry) => {
        router.patch(`/waitlist/${entry.id}/status`, { status: 'booked' })
    }

    const markExpired = (entry) => {
        router.patch(`/waitlist/${entry.id}/status`, { status: 'expired' })
    }

    const remove = (entry) => {
        if (!confirm(`¿Eliminar a "${entry.guest_name}" de la lista de espera?`)) return
        router.delete(`/waitlist/${entry.id}`)
    }

    const formatDate = (dateStr) => {
        return new Date(dateStr + 'T12:00:00').toLocaleDateString('es', {
            day: '2-digit', month: 'short', year: 'numeric',
        })
    }
</script>

<AppLayout title="Lista de Espera">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Lista de Espera</h1>
            <p class="text-gray-400 text-sm mt-1">{waitlist.meta?.total ?? 0} registros</p>
        </div>
        <a
            href="/reservar/lista-espera"
            target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 font-medium rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            Ver formulario público
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label for="wl_search" class="block text-xs text-gray-500 mb-1">Buscar</label>
            <input
                id="wl_search"
                type="text"
                bind:value={search}
                onkeydown={(e) => e.key === 'Enter' && applyFilters()}
                placeholder="Nombre o email..."
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm placeholder-gray-500 focus:outline-none focus:border-amber-500"
            />
        </div>
        <div class="min-w-[150px]">
            <label for="wl_status" class="block text-xs text-gray-500 mb-1">Estado</label>
            <select
                id="wl_status"
                bind:value={statusFilter}
                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
            >
                <option value="">Todos</option>
                {#each statusOptions as opt}
                    <option value={opt.value}>{opt.label}</option>
                {/each}
            </select>
        </div>
        <div class="min-w-[150px]">
            <label for="wl_date" class="block text-xs text-gray-500 mb-1">Fecha preferida</label>
            <input
                id="wl_date"
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
        {#if waitlist.data.length === 0}
            <div class="text-center py-16 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">No hay registros en lista de espera</p>
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Huésped</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Fecha</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Hora</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Pers.</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Estado</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Registrado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        {#each waitlist.data as entry}
                            <tr class="hover:bg-gray-800/30 transition-colors">

                                <!-- Huésped -->
                                <td class="px-5 py-3.5">
                                    <p class="text-white font-medium">{entry.guest_name}</p>
                                    <p class="text-xs text-gray-500">{entry.guest_email}</p>
                                    {#if entry.guest_phone}
                                        <p class="text-xs text-gray-600">{entry.guest_phone}</p>
                                    {/if}
                                    {#if entry.notes}
                                        <p class="text-xs text-amber-400/60 italic mt-0.5 truncate max-w-[200px]">"{entry.notes}"</p>
                                    {/if}
                                </td>

                                <!-- Fecha -->
                                <td class="px-5 py-3.5 text-gray-300 whitespace-nowrap">
                                    {formatDate(entry.preferred_date)}
                                </td>

                                <!-- Hora preferida -->
                                <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">
                                    {entry.preferred_time ?? '—'}
                                </td>

                                <!-- Personas -->
                                <td class="px-5 py-3.5 text-gray-300">{entry.party_size}</td>

                                <!-- Estado -->
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium border {cfg(entry.status).bg}">
                                            <span class="w-1.5 h-1.5 rounded-full {cfg(entry.status).dot}"></span>
                                            {entry.status_label}
                                        </span>
                                        {#if entry.notified_at}
                                            <span class="text-[10px] text-gray-600">Notif. {entry.notified_at}</span>
                                        {/if}
                                    </div>
                                </td>

                                <!-- Fecha de registro -->
                                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                                    {entry.created_at}
                                </td>

                                <!-- Acciones -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3 justify-end">
                                        {#if entry.status === 'pending'}
                                            <button
                                                onclick={() => notify(entry)}
                                                class="text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors whitespace-nowrap"
                                            >
                                                Notificar
                                            </button>
                                        {/if}
                                        {#if entry.status === 'notified'}
                                            <button
                                                onclick={() => markBooked(entry)}
                                                class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors"
                                            >
                                                Marcó reserva
                                            </button>
                                        {/if}
                                        {#if entry.status === 'pending' || entry.status === 'notified'}
                                            <button
                                                onclick={() => markExpired(entry)}
                                                class="text-xs text-gray-500 hover:text-gray-300 transition-colors"
                                            >
                                                Expirar
                                            </button>
                                        {/if}
                                        <button
                                            onclick={() => remove(entry)}
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
            <div class="px-5">
                <Pagination meta={waitlist.meta} only={['waitlist']} />
            </div>
        {/if}
    </div>

</AppLayout>
