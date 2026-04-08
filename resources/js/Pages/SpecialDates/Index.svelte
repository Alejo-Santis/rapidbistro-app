<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { upcoming, byMonth } = $props()

    let modalOpen  = $state(false)
    let isEditing  = $state(false)
    let processing = $state(false)
    let errors     = $state({})

    const defaultForm = () => ({
        id: null,
        date: '',
        name: '',
        type: 'event',
        description: '',
        color: '#f59e0b',
        capacity_override: '',
        booking_allowed: true,
    })

    let form = $state(defaultForm())

    const typeOptions = [
        { value: 'event',   label: 'Evento especial', icon: '🎉' },
        { value: 'blocked', label: 'Fecha bloqueada',  icon: '🚫' },
        { value: 'limited', label: 'Capacidad limitada', icon: '⚠️' },
    ]

    const typeColors = {
        event:   'bg-amber-500/10 text-amber-400 border-amber-500/20',
        blocked: 'bg-red-500/10 text-red-400 border-red-500/20',
        limited: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    }

    const typeLabels = { event: 'Evento', blocked: 'Bloqueado', limited: 'Limitado' }

    const openCreate = () => {
        form      = defaultForm()
        errors    = {}
        isEditing = false
        modalOpen = true
    }

    const openEdit = (sd) => {
        form = {
            id:                sd.id,
            date:              sd.date,
            name:              sd.name,
            type:              sd.type,
            description:       sd.description ?? '',
            color:             sd.color,
            capacity_override: sd.capacity_override ?? '',
            booking_allowed:   sd.booking_allowed,
        }
        errors    = {}
        isEditing = true
        modalOpen = true
    }

    const closeModal = () => {
        modalOpen = false
        form      = defaultForm()
        errors    = {}
    }

    const submit = (e) => {
        e.preventDefault()
        processing = true
        const payload = { ...form }
        if (payload.capacity_override === '') payload.capacity_override = null

        if (isEditing) {
            router.put(`/special-dates/${form.id}`, payload, {
                onError:   (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish:  () => { processing = false },
            })
        } else {
            router.post('/special-dates', payload, {
                onError:   (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish:  () => { processing = false },
            })
        }
    }

    const destroy = (uuid, name) => {
        if (confirm(`¿Eliminar "${name}"?`)) {
            router.delete(`/special-dates/${uuid}`, { preserveScroll: true })
        }
    }

    const formatDate = (dateStr) => {
        const d = new Date(dateStr + 'T12:00:00')
        return d.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })
    }

    const months = $derived(Object.keys(byMonth))
    const monthName = (ym) => {
        const [y, m] = ym.split('-')
        return new Date(parseInt(y), parseInt(m) - 1, 1)
            .toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })
    }
</script>

<AppLayout title="Eventos y fechas especiales">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Eventos y fechas especiales</h1>
            <p class="text-gray-400 text-sm mt-1">Gestiona eventos, bloqueos y capacidades especiales</p>
        </div>
        <button
            onclick={openCreate}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nueva fecha
        </button>
    </div>

    <!-- Próximas fechas -->
    {#if upcoming.length > 0}
        <div class="mb-8">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Próximas fechas</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                {#each upcoming as sd}
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 flex items-start gap-3 group">
                        <!-- Color dot -->
                        <span class="w-3 h-3 rounded-full mt-1 shrink-0" style="background-color:{sd.color}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-white truncate">{sd.name}</p>
                                <span class="shrink-0 text-[11px] px-2 py-0.5 border rounded-full {typeColors[sd.type]}">
                                    {typeLabels[sd.type]}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 capitalize">{formatDate(sd.date)}</p>
                            {#if sd.description}
                                <p class="text-xs text-gray-600 mt-1 truncate italic">{sd.description}</p>
                            {/if}
                            {#if sd.capacity_override}
                                <p class="text-xs text-blue-400 mt-1">Cap. {sd.capacity_override} personas</p>
                            {/if}
                            {#if !sd.booking_allowed}
                                <p class="text-xs text-red-400 mt-1">Sin reservas online</p>
                            {/if}
                        </div>
                        <div class="flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                            <button onclick={() => openEdit(sd)} aria-label="Editar" class="p-1 text-gray-500 hover:text-amber-400 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                            </button>
                            <button onclick={() => destroy(sd.id, sd.name)} aria-label="Eliminar" class="p-1 text-gray-500 hover:text-red-400 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    {/if}

    <!-- Por mes -->
    {#if months.length > 0}
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-4">Historial por mes</h2>
        <div class="space-y-6">
            {#each months as ym}
                <div>
                    <p class="text-sm font-semibold text-gray-400 capitalize mb-2">{monthName(ym)}</p>
                    <div class="divide-y divide-gray-800 bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                        {#each byMonth[ym] as sd}
                            <div class="flex items-center gap-3 px-4 py-3 group hover:bg-gray-800/40 transition-colors">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{sd.color}"></span>
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm text-white">{sd.name}</span>
                                    <span class="ml-2 text-xs text-gray-600">{sd.date}</span>
                                </div>
                                <span class="text-[11px] px-2 py-0.5 border rounded-full {typeColors[sd.type]}">{typeLabels[sd.type]}</span>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick={() => openEdit(sd)} aria-label="Editar" class="p-1 text-gray-500 hover:text-amber-400 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                    </button>
                                    <button onclick={() => destroy(sd.id, sd.name)} aria-label="Eliminar" class="p-1 text-gray-500 hover:text-red-400 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {/each}
        </div>
    {/if}

    {#if upcoming.length === 0 && months.length === 0}
        <div class="bg-gray-900 border border-gray-800 rounded-xl py-20 text-center">
            <svg class="w-14 h-14 mx-auto mb-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <p class="text-gray-500 text-sm">No hay eventos ni fechas especiales registrados</p>
            <button onclick={openCreate} class="mt-3 text-xs text-amber-400 hover:text-amber-300 transition-colors">
                + Crear el primero
            </button>
        </div>
    {/if}

</AppLayout>

<!-- Modal crear/editar -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeModal} aria-label="Cerrar"></button>
        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">{isEditing ? 'Editar fecha' : 'Nueva fecha especial'}</h2>
                <button onclick={closeModal} aria-label="Cerrar modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form onsubmit={submit} class="p-6 space-y-4">

                <!-- Tipo -->
                <div>
                    <p class="block text-sm text-gray-400 mb-2">Tipo *</p>
                    <div class="flex gap-2" role="group" aria-label="Tipo de fecha especial">
                        {#each typeOptions as t}
                            <button
                                type="button"
                                aria-label={t.label}
                                onclick={() => form.type = t.value}
                                class="flex-1 py-2 rounded-lg text-xs font-medium border transition-colors {form.type === t.value ? typeColors[t.value] : 'border-gray-700 text-gray-500 hover:border-gray-600'}"
                            >
                                {t.icon} {t.label}
                            </button>
                        {/each}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="sd_date" class="block text-sm text-gray-400 mb-1.5">Fecha *</label>
                        <input id="sd_date" type="date" bind:value={form.date}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none {errors.date ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                        {#if errors.date}<p class="mt-1 text-xs text-red-400">{errors.date}</p>{/if}
                    </div>
                    <div>
                        <label for="sd_color" class="block text-sm text-gray-400 mb-1.5">Color</label>
                        <div class="flex items-center gap-2">
                            <input id="sd_color" type="color" bind:value={form.color}
                                class="w-10 h-10 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer p-1" />
                            <span class="text-xs text-gray-500 font-mono">{form.color}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="sd_name" class="block text-sm text-gray-400 mb-1.5">Nombre *</label>
                    <input id="sd_name" type="text" bind:value={form.name} placeholder="Ej. Cena de San Valentín"
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                    {#if errors.name}<p class="mt-1 text-xs text-red-400">{errors.name}</p>{/if}
                </div>

                <div>
                    <label for="sd_desc" class="block text-sm text-gray-400 mb-1.5">Descripción</label>
                    <textarea id="sd_desc" bind:value={form.description} rows="2" placeholder="Detalles del evento..."
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none resize-none"></textarea>
                </div>

                {#if form.type === 'limited'}
                    <div>
                        <label for="sd_cap" class="block text-sm text-gray-400 mb-1.5">Capacidad máxima</label>
                        <input id="sd_cap" type="number" min="1" bind:value={form.capacity_override} placeholder="Dejar vacío = sin límite especial"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                    </div>
                {/if}

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        onclick={() => form.booking_allowed = !form.booking_allowed}
                        class="relative w-10 h-6 rounded-full transition-colors {form.booking_allowed ? 'bg-amber-500' : 'bg-gray-700'}"
                        role="switch"
                        aria-checked={form.booking_allowed}
                        aria-label="Permitir reservas online"
                    >
                        <span class="absolute top-1 w-4 h-4 rounded-full bg-white shadow transition-transform {form.booking_allowed ? 'translate-x-5' : 'translate-x-1'}"></span>
                    </button>
                    <span class="text-sm text-gray-300">Reservas online permitidas</span>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors">
                        {processing ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear fecha')}
                    </button>
                    <button type="button" onclick={closeModal}
                        class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
