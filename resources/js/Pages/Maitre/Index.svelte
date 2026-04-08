<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { reservations, waitingWalkIns, stats, freeTables, now } = $props()

    let activeTab    = $state('upcoming') // upcoming | seated | all | walkin
    let detailOpen   = $state(false)
    let selected     = $state(null)
    let processing   = $state(false)

    const statusColors = {
        pending:   'border-amber-500/50 bg-amber-500/5',
        confirmed: 'border-blue-500/50 bg-blue-500/5',
        seated:    'border-emerald-500/50 bg-emerald-500/5',
        completed: 'border-gray-600/50 bg-gray-800/30',
        no_show:   'border-red-500/30 bg-red-500/5',
    }

    const badgeColors = {
        pending:   'bg-amber-500/15 text-amber-400 border-amber-500/30',
        confirmed: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
        seated:    'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        completed: 'bg-gray-700 text-gray-400 border-gray-600',
        no_show:   'bg-red-500/15 text-red-400 border-red-500/30',
    }

    const statusLabel = {
        pending: 'Pendiente', confirmed: 'Confirmada',
        seated: 'En mesa', completed: 'Completada', no_show: 'No show',
    }

    const filteredList = $derived(() => {
        if (activeTab === 'seated')   return reservations.filter(r => r.status === 'seated')
        if (activeTab === 'upcoming') return reservations.filter(r => ['pending','confirmed'].includes(r.status))
        if (activeTab === 'walkin')   return waitingWalkIns
        return reservations
    })

    const openDetail = (r) => {
        selected   = r
        detailOpen = true
    }

    const closeDetail = () => {
        detailOpen = false
        selected   = null
    }

    const changeStatus = (uuid, status) => {
        processing = true
        router.patch(`/reservations/${uuid}/status`, { status }, {
            preserveScroll: true,
            onSuccess: () => closeDetail(),
            onFinish:  () => { processing = false },
        })
    }

    const seatWalkIn = (uuid) => {
        router.patch(`/walk-in/${uuid}/seat`, {}, { preserveScroll: true })
    }

    const waitColor = (min) => {
        if (min >= 30) return 'text-red-400'
        if (min >= 15) return 'text-orange-400'
        return 'text-emerald-400'
    }

    const tabs = [
        { id: 'upcoming', label: 'Por llegar', count: () => stats.pending + stats.confirmed },
        { id: 'seated',   label: 'En mesa',    count: () => stats.seated },
        { id: 'walkin',   label: 'Walk-in',    count: () => stats.walk_ins },
        { id: 'all',      label: 'Todas',      count: () => stats.total },
    ]
</script>

<AppLayout title="Vista Maître">

    <!-- Cabecera compacta -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-white">Vista Maître</h1>
            <p class="text-xs text-gray-500 mt-0.5">{now} · {freeTables} mesas libres</p>
        </div>
        <a href="/floor-map" class="p-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-gray-400 transition-colors" aria-label="Mapa de mesas">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
        </a>
    </div>

    <!-- KPIs rápidos — scroll horizontal en móvil -->
    <div class="flex gap-2 overflow-x-auto pb-1 mb-4 scrollbar-hide">
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-center shrink-0">
            <p class="text-2xl font-bold text-white">{stats.total}</p>
            <p class="text-[10px] text-gray-500">Total</p>
        </div>
        <div class="bg-gray-900 border border-blue-500/20 rounded-xl px-4 py-3 text-center shrink-0">
            <p class="text-2xl font-bold text-blue-400">{stats.confirmed}</p>
            <p class="text-[10px] text-gray-500">Confirmadas</p>
        </div>
        <div class="bg-gray-900 border border-emerald-500/20 rounded-xl px-4 py-3 text-center shrink-0">
            <p class="text-2xl font-bold text-emerald-400">{stats.seated}</p>
            <p class="text-[10px] text-gray-500">En mesa</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-center shrink-0">
            <p class="text-2xl font-bold text-white">{stats.covers}</p>
            <p class="text-[10px] text-gray-500">Comensales</p>
        </div>
        {#if stats.no_show > 0}
            <div class="bg-gray-900 border border-red-500/20 rounded-xl px-4 py-3 text-center shrink-0">
                <p class="text-2xl font-bold text-red-400">{stats.no_show}</p>
                <p class="text-[10px] text-gray-500">No-show</p>
            </div>
        {/if}
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 bg-gray-900 border border-gray-800 rounded-xl p-1 mb-4">
        {#each tabs as tab}
            <button
                onclick={() => activeTab = tab.id}
                class="flex-1 py-2 px-2 rounded-lg text-xs font-medium transition-colors {activeTab === tab.id ? 'bg-amber-500 text-gray-900' : 'text-gray-400 hover:text-white'}"
            >
                {tab.label}
                {#if tab.count() > 0}
                    <span class="ml-1 {activeTab === tab.id ? 'text-gray-700' : 'text-gray-600'}">{tab.count()}</span>
                {/if}
            </button>
        {/each}
    </div>

    <!-- Lista de reservas -->
    {#if activeTab === 'walkin'}
        <!-- Walk-ins -->
        {#if waitingWalkIns.length === 0}
            <div class="text-center py-16 text-gray-600 text-sm">No hay grupos esperando presencialmente</div>
        {:else}
            <div class="space-y-2">
                {#each waitingWalkIns as w}
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-center gap-3">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-white">{w.name}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{w.party_size} personas</p>
                            {#if w.notes}<p class="text-xs text-gray-600 italic mt-0.5">"{w.notes}"</p>{/if}
                        </div>
                        <span class="text-sm font-bold {waitColor(w.wait_min)} font-mono">{w.wait_min}min</span>
                        <button
                            onclick={() => seatWalkIn(w.uuid)}
                            class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg text-xs font-medium transition-colors"
                        >
                            Sentar
                        </button>
                    </div>
                {/each}
            </div>
        {/if}

    {:else}
        <!-- Reservas -->
        {#if filteredList().length === 0}
            <div class="text-center py-16 text-gray-600 text-sm">No hay reservas en este estado</div>
        {:else}
            <div class="space-y-2">
                {#each filteredList() as r}
                    <button
                        onclick={() => openDetail(r)}
                        class="w-full text-left bg-gray-900 border rounded-xl p-4 transition-colors hover:border-gray-600 {statusColors[r.status] ?? 'border-gray-800'} {r.is_soon ? 'ring-1 ring-amber-500/30' : ''}"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Hora -->
                            <div class="shrink-0 text-center w-12">
                                <p class="text-sm font-bold font-mono text-white">{r.starts_at}</p>
                                <p class="text-[10px] text-gray-600">{r.ends_at}</p>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-white truncate">{r.guest_name}</p>
                                    {#if r.is_soon}
                                        <span class="text-[10px] bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded-full shrink-0">Pronto</span>
                                    {/if}
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {r.party_size} personas
                                    {#if r.table_number} · Mesa {r.table_number}{/if}
                                    {#if r.zone_name} · {r.zone_name}{/if}
                                </p>
                                {#if r.notes}
                                    <p class="text-xs text-gray-600 italic mt-1 truncate">"{r.notes}"</p>
                                {/if}
                            </div>

                            <!-- Estado -->
                            <span class="shrink-0 text-[11px] px-2 py-0.5 border rounded-full {badgeColors[r.status] ?? ''}">
                                {statusLabel[r.status] ?? r.status}
                            </span>
                        </div>
                    </button>
                {/each}
            </div>
        {/if}
    {/if}

</AppLayout>

<!-- Panel de acciones rápidas -->
{#if detailOpen && selected}
    <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <button class="absolute inset-0 bg-black/70" onclick={closeDetail} aria-label="Cerrar"></button>
        <div class="relative bg-gray-900 border border-gray-800 rounded-t-2xl sm:rounded-2xl w-full sm:max-w-sm shadow-2xl">

            <!-- Handle bar (móvil) -->
            <div class="flex justify-center pt-3 pb-1 sm:hidden">
                <div class="w-10 h-1 bg-gray-700 rounded-full"></div>
            </div>

            <div class="px-5 py-4 border-b border-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-bold text-white text-lg">{selected.guest_name}</p>
                        <p class="text-sm text-gray-400 mt-0.5">
                            {selected.starts_at}–{selected.ends_at} · {selected.party_size} personas
                        </p>
                        {#if selected.table_number}
                            <p class="text-xs text-gray-500 mt-0.5">Mesa {selected.table_number} · {selected.zone_name}</p>
                        {/if}
                    </div>
                    <button onclick={closeDetail} aria-label="Cerrar" class="text-gray-600 hover:text-white transition-colors mt-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                {#if selected.notes}
                    <p class="text-xs text-gray-500 mt-2 italic">"{selected.notes}"</p>
                {/if}
                {#if selected.internal_notes}
                    <p class="text-xs text-amber-400/70 mt-1 italic">📋 {selected.internal_notes}</p>
                {/if}
                {#if selected.guest_phone}
                    <a href="tel:{selected.guest_phone}" class="text-xs text-blue-400 mt-1 block">{selected.guest_phone}</a>
                {/if}
            </div>

            <!-- Acciones según estado -->
            <div class="p-4 space-y-2">
                {#if selected.status === 'pending' || selected.status === 'confirmed'}
                    <button
                        disabled={processing}
                        onclick={() => changeStatus(selected.uuid, 'seated')}
                        class="w-full py-3.5 bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-white font-bold rounded-xl text-sm transition-colors"
                    >
                        ✓ Sentar cliente
                    </button>
                    <button
                        disabled={processing}
                        onclick={() => changeStatus(selected.uuid, 'confirmed')}
                        class="w-full py-3 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/20 font-medium rounded-xl text-sm transition-colors {selected.status === 'confirmed' ? 'opacity-40 cursor-default' : ''}"
                    >
                        Confirmar llegada
                    </button>
                    <button
                        disabled={processing}
                        onclick={() => changeStatus(selected.uuid, 'no_show')}
                        class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 font-medium rounded-xl text-sm transition-colors"
                    >
                        No se presentó
                    </button>
                {/if}

                {#if selected.status === 'seated'}
                    <button
                        disabled={processing}
                        onclick={() => changeStatus(selected.uuid, 'completed')}
                        class="w-full py-3.5 bg-gray-600 hover:bg-gray-500 disabled:opacity-50 text-white font-bold rounded-xl text-sm transition-colors"
                    >
                        ✓ Marcar como completada
                    </button>
                {/if}

                {#if selected.status === 'completed' || selected.status === 'no_show'}
                    <p class="text-center text-xs text-gray-600 py-4">Esta reserva ya está finalizada</p>
                {/if}

                <button onclick={closeDetail} class="w-full py-2.5 text-gray-500 text-sm hover:text-gray-300 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
{/if}
