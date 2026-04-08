<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { zones, selectedDate, totals } = $props()

    let date = $state(selectedDate)
    let activeTable = $state(null)
    let detailOpen = $state(false)

    const statusConfig = {
        available:   { bg: 'bg-emerald-500/15 hover:bg-emerald-500/25 border-emerald-500/30', badge: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30', dot: 'bg-emerald-400', label: 'Disponible' },
        reserved:    { bg: 'bg-blue-500/15 hover:bg-blue-500/25 border-blue-500/30',          badge: 'bg-blue-500/15 text-blue-400 border-blue-500/30',          dot: 'bg-blue-400',    label: 'Reservada'   },
        occupied:    { bg: 'bg-amber-500/15 hover:bg-amber-500/25 border-amber-500/30',       badge: 'bg-amber-500/15 text-amber-400 border-amber-500/30',       dot: 'bg-amber-400',   label: 'Ocupada'     },
        maintenance: { bg: 'bg-orange-500/15 hover:bg-orange-500/25 border-orange-500/30',   badge: 'bg-orange-500/15 text-orange-400 border-orange-500/30',   dot: 'bg-orange-400',  label: 'Mantenim.'   },
        unavailable: { bg: 'bg-gray-500/15 hover:bg-gray-500/25 border-gray-500/30',         badge: 'bg-gray-500/15 text-gray-400 border-gray-500/30',         dot: 'bg-gray-500',    label: 'No disp.'    },
    }

    const locationIcons = {
        indoor:   'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        outdoor:  'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        rooftop:  'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
        bar:      'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        private:  'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        lounge:   'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    }

    const statCards = $derived([
        { label: 'Total mesas',  value: totals.total,       color: 'text-white'        },
        { label: 'Disponibles',  value: totals.available,   color: 'text-emerald-400'  },
        { label: 'Reservadas',   value: totals.reserved,    color: 'text-blue-400'     },
        { label: 'Ocupadas',     value: totals.occupied,    color: 'text-amber-400'    },
        { label: 'No disp.',     value: totals.unavailable, color: 'text-gray-400'     },
    ])

    const occupancyPct = $derived(
        totals.total > 0
            ? Math.round(((totals.reserved + totals.occupied) / totals.total) * 100)
            : 0
    )

    const changeDate = () => {
        router.get('/floor-map', { date }, { preserveState: true, replace: true })
    }

    const openDetail = (table) => {
        activeTable = table
        detailOpen = true
    }

    const closeDetail = () => {
        detailOpen = false
        activeTable = null
    }

    const goToNewReservation = (table) => {
        router.get('/reservations/create', { table_id: table.id })
    }

    const goToReservation = (uuid) => {
        router.get(`/reservations/${uuid}/edit`)
    }

    const getNextReservation = (table) => {
        return table.reservations?.[0] ?? null
    }

    const cfg = (status) => statusConfig[status] ?? statusConfig.unavailable
</script>

<AppLayout title="Mapa de Mesas">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Mapa de Mesas</h1>
            <p class="text-gray-400 text-sm mt-1">Vista en tiempo real del salón</p>
        </div>

        <!-- Selector de fecha -->
        <div class="flex items-center gap-2">
            <input
                type="date"
                bind:value={date}
                onchange={changeDate}
                class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-amber-500"
            />
            <button
                onclick={() => { date = new Date().toISOString().split('T')[0]; changeDate() }}
                class="px-3 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-sm text-gray-300 transition-colors"
            >
                Hoy
            </button>
        </div>
    </div>

    <!-- Stats bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        {#each statCards as card}
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-center">
                <p class="text-2xl font-bold {card.color}">{card.value}</p>
                <p class="text-xs text-gray-500 mt-0.5">{card.label}</p>
            </div>
        {/each}
    </div>

    <!-- Barra de ocupación -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-400">Ocupación del día</span>
            <span class="text-sm font-semibold text-white">{occupancyPct}%</span>
        </div>
        <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
            <div
                class="h-full rounded-full transition-all duration-500
                    {occupancyPct >= 80 ? 'bg-red-500' : occupancyPct >= 50 ? 'bg-amber-500' : 'bg-emerald-500'}"
                style="width: {occupancyPct}%"
            ></div>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="flex flex-wrap gap-4 mb-6">
        {#each Object.entries(statusConfig) as [key, val]}
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full {val.dot}"></span>
                <span class="text-xs text-gray-400">{val.label}</span>
            </div>
        {/each}
    </div>

    <!-- Zonas y mesas -->
    {#if zones.length === 0}
        <div class="text-center py-20 text-gray-500">
            <p class="text-sm">No hay zonas activas configuradas.</p>
        </div>
    {:else}
        <div class="space-y-8">
            {#each zones as zone}
                <section>
                    <!-- Cabecera de zona -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-gray-800 rounded-lg">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d={locationIcons[zone.location] ?? locationIcons.indoor} />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white">{zone.name}</h2>
                            <p class="text-xs text-gray-500">{zone.location_label} · {zone.tables.length} mesas</p>
                        </div>
                        <div class="ml-auto h-px flex-1 bg-gray-800"></div>
                    </div>

                    <!-- Grid de mesas -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                        {#each zone.tables as table}
                            {@const config = cfg(table.status)}
                            {@const next = getNextReservation(table)}
                            <button
                                onclick={() => openDetail(table)}
                                class="relative flex flex-col items-start text-left p-4 rounded-xl border transition-all duration-150 cursor-pointer
                                    {config.bg}"
                            >
                                <!-- Número de mesa -->
                                <div class="flex items-center justify-between w-full mb-2">
                                    <span class="text-lg font-bold text-white leading-none">#{table.number}</span>
                                    <span class="w-2 h-2 rounded-full {config.dot}"></span>
                                </div>

                                <!-- Capacidad -->
                                <div class="flex items-center gap-1 text-gray-400 mb-2.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-xs">{table.min_capacity}–{table.capacity}</span>
                                </div>

                                <!-- Info reserva si existe -->
                                {#if next}
                                    <div class="w-full pt-2 border-t border-white/5">
                                        <p class="text-xs font-medium text-white truncate">{next.guest_name}</p>
                                        <p class="text-xs text-gray-400">{next.starts_at} · {next.party_size} pax</p>
                                    </div>
                                {/if}

                                <!-- Badge estado -->
                                <span class="mt-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium border {config.badge}">
                                    {table.status_label}
                                </span>
                            </button>
                        {/each}
                    </div>
                </section>
            {/each}
        </div>
    {/if}

</AppLayout>

<!-- Panel de detalle de mesa -->
{#if detailOpen && activeTable}
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
        <button
            class="absolute inset-0 bg-black/60"
            onclick={closeDetail}
            aria-label="Cerrar"
        ></button>

        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-sm shadow-2xl">

            <!-- Header del panel -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg {cfg(activeTable.status).bg} border {cfg(activeTable.status).badge} flex items-center justify-center">
                        <span class="text-sm font-bold text-white">#{activeTable.number}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Mesa #{activeTable.number}</p>
                        <p class="text-xs text-gray-400">{activeTable.min_capacity}–{activeTable.capacity} personas</p>
                    </div>
                </div>
                <button onclick={closeDetail} class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">

                <!-- Estado actual -->
                <div>
                    <p class="text-xs text-gray-500 mb-1.5">Estado actual</p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {cfg(activeTable.status).badge}">
                        <span class="w-1.5 h-1.5 rounded-full {cfg(activeTable.status).dot}"></span>
                        {activeTable.status_label}
                    </span>
                </div>

                <!-- Reservaciones del día -->
                {#if activeTable.reservations?.length > 0}
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Reservaciones del día ({activeTable.reservations.length})</p>
                        <div class="space-y-2">
                            {#each activeTable.reservations as r}
                                <button
                                    onclick={() => goToReservation(r.uuid)}
                                    class="w-full text-left px-3 py-2.5 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors"
                                >
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-sm font-medium text-white">{r.guest_name}</span>
                                        <span class="text-xs text-gray-400">{r.starts_at}–{r.ends_at}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-400">{r.party_size} personas</span>
                                        <span class="text-xs text-gray-500">#{r.confirmation_code}</span>
                                        <span class="ml-auto inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] border {cfg(r.status).badge}">
                                            {r.status_label}
                                        </span>
                                    </div>
                                    {#if r.internal_notes}
                                        <p class="mt-1 text-xs text-amber-400/70 italic truncate">"{r.internal_notes}"</p>
                                    {/if}
                                </button>
                            {/each}
                        </div>
                    </div>
                {:else}
                    <div class="py-4 text-center text-gray-500">
                        <svg class="w-8 h-8 mx-auto mb-2 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-xs">Sin reservaciones para este día</p>
                    </div>
                {/if}

                <!-- Acción -->
                <button
                    onclick={() => goToNewReservation(activeTable)}
                    class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                >
                    + Nueva reservación
                </button>
            </div>
        </div>
    </div>
{/if}
