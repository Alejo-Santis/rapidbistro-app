<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { slots, daysOfWeek } = $props()

    let modalOpen  = $state(false)
    let editingSlot = $state(null)
    let processing  = $state(false)
    let errors      = $state({})

    const defaultForm = () => ({
        day_of_week:                 daysOfWeek[0]?.value ?? 'monday',
        name:                        '',
        opens_at:                    '12:00',
        closes_at:                   '23:00',
        slot_duration_minutes:       90,
        max_concurrent_reservations: 5,
    })

    let form = $state(defaultForm())

    const SERVICE_PRESETS = [
        { label: 'Almuerzo',  opens_at: '12:00', closes_at: '16:00' },
        { label: 'Merienda',  opens_at: '16:00', closes_at: '19:00' },
        { label: 'Cena',      opens_at: '19:00', closes_at: '23:30' },
        { label: 'Desayuno',  opens_at: '07:00', closes_at: '11:30' },
        { label: 'Brunch',    opens_at: '09:00', closes_at: '14:00' },
    ]

    const DAY_COLORS = {
        monday:    'bg-blue-500/10 text-blue-400 border-blue-500/20',
        tuesday:   'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        wednesday: 'bg-violet-500/10 text-violet-400 border-violet-500/20',
        thursday:  'bg-purple-500/10 text-purple-400 border-purple-500/20',
        friday:    'bg-pink-500/10 text-pink-400 border-pink-500/20',
        saturday:  'bg-amber-500/10 text-amber-400 border-amber-500/20',
        sunday:    'bg-orange-500/10 text-orange-400 border-orange-500/20',
    }

    // Agrupa slots por día
    const slotsByDay = $derived(() => {
        const map = new Map()
        for (const day of daysOfWeek) map.set(day.value, [])
        for (const s of slots) {
            if (map.has(s.day_of_week)) map.get(s.day_of_week).push(s)
        }
        return map
    })

    const totalTurnos = $derived(slots.length)

    const calcSlots = (slot) => {
        if (!slot.opens_at || !slot.closes_at || !slot.slot_duration_minutes) return '—'
        const [oh, om] = slot.opens_at.split(':').map(Number)
        const [ch, cm] = slot.closes_at.split(':').map(Number)
        const totalMins = (ch * 60 + cm) - (oh * 60 + om)
        const count = Math.floor(totalMins / slot.slot_duration_minutes)
        return `~${count} turnos`
    }

    const openCreate = (dayValue = null) => {
        editingSlot = null
        form = {
            ...defaultForm(),
            day_of_week: dayValue ?? daysOfWeek[0]?.value ?? 'monday',
        }
        errors = {}
        modalOpen = true
    }

    const openEdit = (slot) => {
        editingSlot = slot
        form = {
            day_of_week:                 slot.day_of_week,
            name:                        slot.name ?? '',
            opens_at:                    slot.opens_at,
            closes_at:                   slot.closes_at,
            slot_duration_minutes:       slot.slot_duration_minutes,
            max_concurrent_reservations: slot.max_concurrent_reservations,
        }
        errors = {}
        modalOpen = true
    }

    const closeModal = () => {
        modalOpen   = false
        editingSlot = null
        form        = defaultForm()
        errors      = {}
    }

    const applyPreset = (preset) => {
        form.name      = preset.label
        form.opens_at  = preset.opens_at
        form.closes_at = preset.closes_at
    }

    const submit = (e) => {
        e.preventDefault()
        processing = true
        if (editingSlot) {
            router.put(`/time-slots/${editingSlot.id}`, { ...form }, {
                onError:   (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish:  () => { processing = false },
            })
        } else {
            router.post('/time-slots', { ...form }, {
                onError:   (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish:  () => { processing = false },
            })
        }
    }

    const deleteSlot = (slot) => {
        if (confirm(`¿Eliminar el turno "${slot.name || slot.opens_at + '–' + slot.closes_at}" del ${slot.day_label}?`)) {
            router.delete(`/time-slots/${slot.id}`)
        }
    }
</script>

<AppLayout title="Horarios">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Horarios y Turnos</h1>
            <p class="text-gray-400 text-sm mt-1">{totalTurnos} turno{totalTurnos !== 1 ? 's' : ''} configurado{totalTurnos !== 1 ? 's' : ''}</p>
        </div>
        <button
            onclick={() => openCreate()}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo turno
        </button>
    </div>

    <!-- Presets de servicio info -->
    <div class="mb-5 p-4 bg-gray-900 border border-gray-800 rounded-xl">
        <p class="text-xs text-gray-500">
            Puedes crear múltiples turnos por día (ej. Almuerzo 12:00–16:00 y Cena 19:00–23:30).
            Cada turno define el horario de atención para ese servicio.
        </p>
    </div>

    <!-- Grid de días -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        {#each daysOfWeek as day}
            {@const daySlots = slotsByDay().get(day.value) ?? []}
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">

                <!-- Header del día -->
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {DAY_COLORS[day.value]}">
                        {day.label}
                    </span>
                    <button
                        onclick={() => openCreate(day.value)}
                        class="text-xs text-amber-400 hover:text-amber-300 transition-colors flex items-center gap-1"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar
                    </button>
                </div>

                {#if daySlots.length === 0}
                    <div class="text-center py-6 border border-dashed border-gray-700 rounded-lg">
                        <p class="text-xs text-gray-600">Sin turnos configurados</p>
                    </div>
                {:else}
                    <div class="space-y-2.5">
                        {#each daySlots as slot}
                            <div class="bg-gray-800/60 border border-gray-700/50 rounded-lg p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        {#if slot.name}
                                            <p class="text-sm font-semibold text-white">{slot.name}</p>
                                        {/if}
                                        <p class="font-mono text-xs text-amber-400">{slot.opens_at} – {slot.closes_at}</p>
                                    </div>
                                    <div class="flex gap-2 ml-2 shrink-0">
                                        <button onclick={() => openEdit(slot)} class="text-xs text-gray-400 hover:text-amber-400 transition-colors">Editar</button>
                                        <button onclick={() => deleteSlot(slot)} class="text-xs text-gray-400 hover:text-red-400 transition-colors">✕</button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span>{slot.slot_duration_minutes} min/turno</span>
                                    <span>·</span>
                                    <span>Máx. {slot.max_concurrent_reservations} simult.</span>
                                    <span>·</span>
                                    <span class="text-amber-500/70">{calcSlots(slot)}</span>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}

            </div>
        {/each}
    </div>

</AppLayout>

<!-- Modal -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeModal} aria-label="Cerrar"></button>

        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">{editingSlot ? 'Editar turno' : 'Nuevo turno'}</h2>
                <button onclick={closeModal} aria-label="Cerrar modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form onsubmit={submit} class="p-6 space-y-4">

                <!-- Presets rápidos -->
                {#if !editingSlot}
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Presets rápidos</p>
                        <div class="flex flex-wrap gap-2">
                            {#each SERVICE_PRESETS as preset}
                                <button
                                    type="button"
                                    onclick={() => applyPreset(preset)}
                                    class="px-2.5 py-1 rounded-lg text-xs border border-gray-700 text-gray-400 hover:border-amber-500 hover:text-amber-400 transition-colors"
                                >
                                    {preset.label}
                                </button>
                            {/each}
                        </div>
                    </div>
                {/if}

                <!-- Día -->
                <div>
                    <label for="ts_day" class="block text-sm text-gray-400 mb-1.5">Día de la semana *</label>
                    <select
                        id="ts_day"
                        bind:value={form.day_of_week}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                    >
                        {#each daysOfWeek as d}
                            <option value={d.value}>{d.label}</option>
                        {/each}
                    </select>
                    {#if errors.day_of_week}
                        <p class="mt-1 text-xs text-red-400">{errors.day_of_week}</p>
                    {/if}
                </div>

                <!-- Nombre del servicio -->
                <div>
                    <label for="ts_name" class="block text-sm text-gray-400 mb-1.5">Nombre del servicio</label>
                    <input
                        id="ts_name"
                        type="text"
                        bind:value={form.name}
                        placeholder="Ej. Almuerzo, Cena, Brunch..."
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 placeholder-gray-600"
                    />
                    {#if errors.name}
                        <p class="mt-1 text-xs text-red-400">{errors.name}</p>
                    {/if}
                </div>

                <!-- Horario -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="ts_opens" class="block text-sm text-gray-400 mb-1.5">Apertura *</label>
                        <input
                            id="ts_opens"
                            type="time"
                            bind:value={form.opens_at}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {errors.opens_at ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                        />
                        {#if errors.opens_at}
                            <p class="mt-1 text-xs text-red-400">{errors.opens_at}</p>
                        {/if}
                    </div>
                    <div>
                        <label for="ts_closes" class="block text-sm text-gray-400 mb-1.5">Cierre *</label>
                        <input
                            id="ts_closes"
                            type="time"
                            bind:value={form.closes_at}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {errors.closes_at ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                        />
                        {#if errors.closes_at}
                            <p class="mt-1 text-xs text-red-400">{errors.closes_at}</p>
                        {/if}
                    </div>
                </div>

                <!-- Configuración -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="ts_duration" class="block text-sm text-gray-400 mb-1.5">Duración turno (min) *</label>
                        <input
                            id="ts_duration"
                            type="number"
                            bind:value={form.slot_duration_minutes}
                            min="15" max="480" step="15"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        />
                        {#if errors.slot_duration_minutes}
                            <p class="mt-1 text-xs text-red-400">{errors.slot_duration_minutes}</p>
                        {/if}
                    </div>
                    <div>
                        <label for="ts_max" class="block text-sm text-gray-400 mb-1.5">Máx. simultáneas *</label>
                        <input
                            id="ts_max"
                            type="number"
                            bind:value={form.max_concurrent_reservations}
                            min="1" max="100"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        />
                        {#if errors.max_concurrent_reservations}
                            <p class="mt-1 text-xs text-red-400">{errors.max_concurrent_reservations}</p>
                        {/if}
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                    >
                        {processing ? 'Guardando...' : (editingSlot ? 'Actualizar' : 'Crear turno')}
                    </button>
                    <button
                        type="button"
                        onclick={closeModal}
                        class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
