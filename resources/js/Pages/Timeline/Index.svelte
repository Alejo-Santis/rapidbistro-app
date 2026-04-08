<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { tables, zones, columns, opensAt, closesAt, selectedDate, selectedZone } = $props()

    let date    = $state(selectedDate)
    let zoneId  = $state(selectedZone ?? '')
    let tooltip = $state(null)   // { reservation, x, y }

    const STATUS_CFG = {
        pending:   { bg: 'bg-gray-500/40 border-gray-400/40 hover:bg-gray-500/60',     text: 'text-gray-100' },
        confirmed: { bg: 'bg-blue-500/40 border-blue-400/40 hover:bg-blue-500/60',     text: 'text-blue-100' },
        seated:    { bg: 'bg-amber-500/40 border-amber-400/40 hover:bg-amber-500/60',  text: 'text-amber-100' },
        completed: { bg: 'bg-emerald-500/40 border-emerald-400/40 hover:bg-emerald-500/60', text: 'text-emerald-100' },
        no_show:   { bg: 'bg-red-500/40 border-red-400/40 hover:bg-redged-500/60',    text: 'text-red-100' },
    }

    const ROW_HEIGHT = 56   // px por fila de mesa
    const MIN_COL_W  = 56   // px por columna de 30 min

    const cfgFor = (status) => STATUS_CFG[status] ?? STATUS_CFG.pending

    // Agrupa tablas por zona para mostrar separadores
    const groupedTables = $derived(() => {
        const map = new Map()
        for (const t of tables) {
            if (!map.has(t.zone_name)) map.set(t.zone_name, [])
            map.get(t.zone_name).push(t)
        }
        return [...map.entries()].map(([zone, rows]) => ({ zone, rows }))
    })

    const applyFilters = () => {
        router.get('/timeline', {
            date,
            ...(zoneId ? { zone_id: zoneId } : {}),
        }, { preserveState: true, replace: true })
    }

    const goToReservation = (uuid) => {
        router.get(`/reservations/${uuid}/edit`)
    }

    const showTooltip = (e, reservation) => {
        tooltip = { reservation, x: e.clientX, y: e.clientY }
    }

    const hideTooltip = () => {
        tooltip = null
    }

    const nowPct = $derived(() => {
        const now  = new Date()
        const mins = now.getHours() * 60 + now.getMinutes()
        const [oh, om] = opensAt.split(':').map(Number)
        const [ch, cm] = closesAt.split(':').map(Number)
        const open  = oh * 60 + om
        const close = ch * 60 + cm
        if (mins < open || mins > close) return null
        return ((mins - open) / (close - open)) * 100
    })

    // Día de la semana legible
    const dayLabel = $derived(() => {
        const d = new Date(date + 'T12:00:00')
        return d.toLocaleDateString('es', { weekday: 'long', day: 'numeric', month: 'long' })
    })
</script>

<AppLayout title="Timeline de Turnos">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Timeline de Turnos</h1>
            <p class="text-gray-400 text-sm mt-1 capitalize">{dayLabel()} · {opensAt}–{closesAt}</p>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap items-center gap-2">
            <input
                type="date"
                bind:value={date}
                onchange={applyFilters}
                class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-amber-500"
            />
            <select
                bind:value={zoneId}
                onchange={applyFilters}
                class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-gray-300 focus:outline-none focus:border-amber-500"
            >
                <option value="">Todas las zonas</option>
                {#each zones as z}
                    <option value={z.id}>{z.name}</option>
                {/each}
            </select>
            <button
                onclick={() => { date = new Date().toISOString().split('T')[0]; applyFilters() }}
                class="px-3 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-sm text-gray-300 transition-colors"
            >
                Hoy
            </button>
        </div>
    </div>

    <!-- Leyenda de estados -->
    <div class="flex flex-wrap gap-4 mb-5">
        {#each Object.entries(STATUS_CFG) as [key, val]}
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded border {val.bg}"></span>
                <span class="text-xs text-gray-400 capitalize">{key.replace('_', ' ')}</span>
            </div>
        {/each}
    </div>

    {#if tables.length === 0}
        <div class="text-center py-20 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-sm">No hay mesas para mostrar.</p>
        </div>
    {:else}
        <!-- Grid scrollable -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <div class="min-w-max">

                    <!-- Header de horas -->
                    <div class="flex border-b border-gray-800 sticky top-0 z-10 bg-gray-900">
                        <!-- Columna fija: label -->
                        <div class="w-28 shrink-0 px-3 py-2.5 border-r border-gray-800 flex items-end">
                            <span class="text-xs text-gray-500 font-medium">Mesa</span>
                        </div>
                        <!-- Columnas de hora -->
                        {#each columns as col, i}
                            <div
                                class="shrink-0 flex items-end justify-start px-1.5 py-2"
                                style="width: {MIN_COL_W}px"
                            >
                                <span class="text-xs text-gray-500 {i % 2 === 0 ? 'font-semibold text-gray-400' : ''}">
                                    {i % 2 === 0 ? col : ''}
                                </span>
                            </div>
                        {/each}
                    </div>

                    <!-- Filas por grupo de zona -->
                    {#each groupedTables() as group}
                        <!-- Separador de zona -->
                        <div class="flex items-center bg-gray-800/50 border-b border-gray-800 px-3 py-1.5">
                            <span class="text-xs font-semibold text-amber-400 uppercase tracking-wider">{group.zone}</span>
                        </div>

                        {#each group.rows as table}
                            <div class="flex border-b border-gray-800 last:border-0 group/row hover:bg-gray-800/20 transition-colors"
                                style="height: {ROW_HEIGHT}px"
                            >
                                <!-- Label de mesa (sticky) -->
                                <div class="w-28 shrink-0 flex flex-col justify-center px-3 border-r border-gray-800">
                                    <span class="text-sm font-semibold text-white">#{table.number}</span>
                                    <span class="text-xs text-gray-500">{table.capacity} pax</span>
                                </div>

                                <!-- Área del timeline -->
                                <div
                                    class="relative flex-1"
                                    style="width: {columns.length * MIN_COL_W}px"
                                >
                                    <!-- Líneas verticales de horas -->
                                    {#each columns as _, i}
                                        <div
                                            class="absolute top-0 bottom-0 border-l {i % 2 === 0 ? 'border-gray-700/60' : 'border-gray-800/40'}"
                                            style="left: {(i / columns.length) * 100}%"
                                        ></div>
                                    {/each}

                                    <!-- Línea "ahora" -->
                                    {#if nowPct() !== null}
                                        <div
                                            class="absolute top-0 bottom-0 w-px bg-red-500 z-10"
                                            style="left: {nowPct()}%"
                                        >
                                            <div class="absolute -top-0 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-red-500"></div>
                                        </div>
                                    {/if}

                                    <!-- Bloques de reservación -->
                                    {#each table.reservations as r}
                                        {@const cfg = cfgFor(r.status)}
                                        <button
                                            class="absolute top-1.5 bottom-1.5 rounded border text-left overflow-hidden transition-colors z-20 {cfg.bg}"
                                            style="left: {r.left_pct}%; width: {r.width_pct}%; min-width: 2rem"
                                            onmouseenter={(e) => showTooltip(e, r)}
                                            onmouseleave={hideTooltip}
                                            onclick={() => goToReservation(r.uuid)}
                                            aria-label="Reserva de {r.guest_name}"
                                        >
                                            <div class="px-2 py-1 flex flex-col justify-center h-full">
                                                <p class="text-xs font-medium {cfg.text} leading-tight truncate">{r.guest_name}</p>
                                                <p class="text-[10px] {cfg.text} opacity-70 leading-tight">{r.starts_at}–{r.ends_at} · {r.party_size}px</p>
                                            </div>
                                        </button>
                                    {/each}
                                </div>
                            </div>
                        {/each}
                    {/each}

                </div>
            </div>
        </div>
    {/if}

</AppLayout>

<!-- Tooltip flotante -->
{#if tooltip}
    <div
        class="fixed z-50 pointer-events-none bg-gray-800 border border-gray-700 rounded-xl shadow-2xl px-4 py-3 w-64"
        style="left: {tooltip.x + 12}px; top: {tooltip.y - 8}px; transform: translateY(-50%)"
    >
        <p class="text-sm font-semibold text-white mb-1">{tooltip.reservation.guest_name}</p>
        <div class="space-y-0.5 text-xs text-gray-400">
            <p>{tooltip.reservation.starts_at} – {tooltip.reservation.ends_at}</p>
            <p>{tooltip.reservation.party_size} personas · #{tooltip.reservation.confirmation_code}</p>
            {#if tooltip.reservation.guest_phone}
                <p>{tooltip.reservation.guest_phone}</p>
            {/if}
            {#if tooltip.reservation.internal_notes}
                <p class="mt-1.5 text-amber-400/80 italic">"{tooltip.reservation.internal_notes}"</p>
            {/if}
        </div>
        <div class="mt-2 pt-2 border-t border-gray-700">
            <span class="text-[10px] text-gray-500">Click para editar</span>
        </div>
    </div>
{/if}
