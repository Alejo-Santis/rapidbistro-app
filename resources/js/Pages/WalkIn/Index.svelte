<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { entries } = $props()

    let processing = $state(false)
    let addOpen    = $state(false)
    let errors     = $state({})

    const defaultForm = () => ({ guest_name: '', guest_phone: '', party_size: 2, notes: '' })
    let form = $state(defaultForm())

    const openAdd = () => {
        form   = defaultForm()
        errors = {}
        addOpen = true
    }

    const closeAdd = () => {
        addOpen = false
        form    = defaultForm()
        errors  = {}
    }

    const submit = (e) => {
        e.preventDefault()
        processing = true
        router.post('/walk-in', { ...form }, {
            onError:   (errs) => { errors = errs },
            onSuccess: () => closeAdd(),
            onFinish:  () => { processing = false },
        })
    }

    const seat = (uuid) => {
        router.patch(`/walk-in/${uuid}/seat`, {}, { preserveScroll: true })
    }

    const remove = (uuid, name) => {
        if (confirm(`¿Retirar a "${name}" de la lista?`)) {
            router.patch(`/walk-in/${uuid}/remove`, {}, { preserveScroll: true })
        }
    }

    const waitMinutes = (arrivedAt) => {
        const diff = Math.floor((Date.now() - new Date(arrivedAt)) / 60000)
        if (diff < 60) return `${diff} min`
        return `${Math.floor(diff / 60)}h ${diff % 60}min`
    }

    const waitColor = (arrivedAt) => {
        const diff = Math.floor((Date.now() - new Date(arrivedAt)) / 60000)
        if (diff >= 30) return 'text-red-400'
        if (diff >= 15) return 'text-orange-400'
        return 'text-emerald-400'
    }

    const partyOptions = [1,2,3,4,5,6,7,8,9,10]
</script>

<AppLayout title="Walk-in">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Lista de espera presencial</h1>
            <p class="text-gray-400 text-sm mt-1">
                {entries.length} {entries.length === 1 ? 'grupo esperando' : 'grupos esperando'} ahora
            </p>
        </div>
        <button
            onclick={openAdd}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Agregar grupo
        </button>
    </div>

    {#if entries.length === 0}
        <div class="bg-gray-900 border border-gray-800 rounded-xl py-20 text-center">
            <svg class="w-14 h-14 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="text-gray-500 text-sm">No hay grupos esperando en este momento</p>
            <button onclick={openAdd} class="mt-3 text-xs text-amber-400 hover:text-amber-300 transition-colors">
                + Agregar el primero
            </button>
        </div>
    {:else}
        <div class="space-y-3">
            {#each entries as entry, i}
                <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 flex items-center gap-4">

                    <!-- Posición en cola -->
                    <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-sm font-bold text-gray-400 shrink-0">
                        {i + 1}
                    </div>

                    <!-- Info del grupo -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-white">{entry.guest_name}</p>
                            <span class="text-xs px-2 py-0.5 bg-gray-800 text-gray-400 rounded-full">
                                {entry.party_size} {entry.party_size === 1 ? 'persona' : 'personas'}
                            </span>
                        </div>
                        {#if entry.guest_phone}
                            <p class="text-xs text-gray-500 mt-0.5">{entry.guest_phone}</p>
                        {/if}
                        {#if entry.notes}
                            <p class="text-xs text-gray-600 italic mt-0.5 truncate">"{entry.notes}"</p>
                        {/if}
                    </div>

                    <!-- Tiempo de espera -->
                    <div class="text-right shrink-0">
                        <p class="text-sm font-mono font-bold {waitColor(entry.arrived_at)}">
                            {waitMinutes(entry.arrived_at)}
                        </p>
                        <p class="text-[10px] text-gray-600">esperando</p>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            onclick={() => seat(entry.uuid)}
                            class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg text-xs font-medium transition-colors"
                        >
                            Sentar
                        </button>
                        <button
                            onclick={() => remove(entry.uuid, entry.guest_name)}
                            class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-lg text-xs font-medium transition-colors"
                        >
                            Retirar
                        </button>
                    </div>
                </div>
            {/each}
        </div>

        <!-- Leyenda de tiempos -->
        <div class="flex items-center gap-4 mt-4 text-xs text-gray-600">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> &lt; 15 min</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-400"></span> 15-30 min</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span> &gt; 30 min</span>
        </div>
    {/if}

</AppLayout>

<!-- Modal agregar grupo -->
{#if addOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeAdd} aria-label="Cerrar"></button>
        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">Agregar grupo</h2>
                <button onclick={closeAdd} aria-label="Cerrar modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form onsubmit={submit} class="p-6 space-y-4">
                <div>
                    <label for="wi_name" class="block text-sm text-gray-400 mb-1.5">Nombre *</label>
                    <input id="wi_name" type="text" bind:value={form.guest_name} placeholder="Ej. Familia García"
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none {errors.guest_name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                    {#if errors.guest_name}<p class="mt-1 text-xs text-red-400">{errors.guest_name}</p>{/if}
                </div>
                <div>
                    <label for="wi_phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                    <input id="wi_phone" type="tel" bind:value={form.guest_phone}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                </div>
                <div>
                    <label for="wi_party" class="block text-sm text-gray-400 mb-1.5">Personas *</label>
                    <select id="wi_party" bind:value={form.party_size}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none">
                        {#each partyOptions as n}
                            <option value={n}>{n} {n === 1 ? 'persona' : 'personas'}</option>
                        {/each}
                    </select>
                </div>
                <div>
                    <label for="wi_notes" class="block text-sm text-gray-400 mb-1.5">Notas</label>
                    <input id="wi_notes" type="text" bind:value={form.notes} placeholder="Silla alta, cumpleaños..."
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="submit" disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors">
                        {processing ? 'Agregando...' : 'Agregar a la lista'}
                    </button>
                    <button type="button" onclick={closeAdd}
                        class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
