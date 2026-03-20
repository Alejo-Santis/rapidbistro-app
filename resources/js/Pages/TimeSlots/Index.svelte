<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { slots, daysOfWeek } = $props()

    let modalOpen = $state(false)
    let editingSlot = $state(null)

    let form = $state({
        day_of_week: 'monday',
        opens_at: '12:00',
        closes_at: '23:00',
        slot_duration_minutes: 90,
        max_concurrent_reservations: 5,
    })
    let errors = $state({})
    let processing = $state(false)

    const dayIndex = { monday: 0, tuesday: 1, wednesday: 2, thursday: 3, friday: 4, saturday: 5, sunday: 6 }
    const dayColors = {
        monday:    'bg-blue-500/10 text-blue-400',
        tuesday:   'bg-indigo-500/10 text-indigo-400',
        wednesday: 'bg-violet-500/10 text-violet-400',
        thursday:  'bg-purple-500/10 text-purple-400',
        friday:    'bg-pink-500/10 text-pink-400',
        saturday:  'bg-amber-500/10 text-amber-400',
        sunday:    'bg-orange-500/10 text-orange-400',
    }

    const usedDays = $derived(slots.map(s => s.day_of_week))
    const availableDays = $derived(daysOfWeek.filter(d => !usedDays.includes(d.value) || (editingSlot && editingSlot.day_of_week === d.value)))

    function openCreate(dayValue = null) {
        editingSlot = null
        form = {
            day_of_week: dayValue ?? availableDays[0]?.value ?? 'monday',
            opens_at: '12:00',
            closes_at: '23:00',
            slot_duration_minutes: 90,
            max_concurrent_reservations: 5,
        }
        errors = {}
        modalOpen = true
    }

    function openEdit(slot) {
        editingSlot = slot
        form = {
            day_of_week: slot.day_of_week,
            opens_at: slot.opens_at,
            closes_at: slot.closes_at,
            slot_duration_minutes: slot.slot_duration_minutes,
            max_concurrent_reservations: slot.max_concurrent_reservations,
        }
        errors = {}
        modalOpen = true
    }

    function closeModal() {
        modalOpen = false
        editingSlot = null
        form = { day_of_week: 'monday', opens_at: '12:00', closes_at: '23:00', slot_duration_minutes: 90, max_concurrent_reservations: 5 }
        errors = {}
    }

    function submit(e) {
        e.preventDefault()
        processing = true
        if (editingSlot) {
            router.put(`/time-slots/${editingSlot.id}`, { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        } else {
            router.post('/time-slots', { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        }
    }

    function deleteSlot(slot) {
        if (confirm(`¿Eliminar el horario del ${slot.day_label}?`)) {
            router.delete(`/time-slots/${slot.id}`)
        }
    }

    function calculateSlots(slot) {
        if (!slot.opens_at || !slot.closes_at || !slot.slot_duration_minutes) return '—'
        const [oh, om] = slot.opens_at.split(':').map(Number)
        const [ch, cm] = slot.closes_at.split(':').map(Number)
        const totalMins = (ch * 60 + cm) - (oh * 60 + om)
        const count = Math.floor(totalMins / slot.slot_duration_minutes)
        return `~${count} turnos`
    }
</script>

<AppLayout title="Horarios">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Horarios de atención</h1>
            <p class="text-gray-400 text-sm mt-1">
                {slots.length}/7 días configurados
            </p>
        </div>
        {#if availableDays.length > 0 || editingSlot}
            <button
                onclick={() => openCreate()}
                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Agregar horario
            </button>
        {/if}
    </div>

    <!-- Grid de días de la semana -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {#each daysOfWeek as day}
            {@const slot = slots.find(s => s.day_of_week === day.value)}
            <div class="bg-gray-900 border rounded-xl p-4 transition-colors {slot ? 'border-gray-800' : 'border-dashed border-gray-700 opacity-60'}">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {dayColors[day.value] ?? 'bg-gray-700 text-gray-400'}">
                        {day.label}
                    </span>
                    {#if slot}
                        <div class="flex gap-2">
                            <button onclick={() => openEdit(slot)} class="text-xs text-amber-400 hover:text-amber-300 transition-colors">Editar</button>
                            <button onclick={() => deleteSlot(slot)} class="text-xs text-red-400 hover:text-red-300 transition-colors">✕</button>
                        </div>
                    {/if}
                </div>

                {#if slot}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Horario</span>
                            <span class="text-sm font-mono text-white">{slot.opens_at} – {slot.closes_at}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Duración turno</span>
                            <span class="text-sm text-gray-300">{slot.slot_duration_minutes} min</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Máx. simultáneas</span>
                            <span class="text-sm text-gray-300">{slot.max_concurrent_reservations}</span>
                        </div>
                        <div class="pt-1 border-t border-gray-800">
                            <span class="text-xs text-amber-500">{calculateSlots(slot)}</span>
                        </div>
                    </div>
                {:else}
                    <div class="text-center py-4">
                        <p class="text-xs text-gray-600">Sin configurar</p>
                        <button
                            onclick={() => openCreate(day.value)}
                            class="mt-2 text-xs text-amber-400 hover:text-amber-300 transition-colors"
                        >
                            + Configurar
                        </button>
                    </div>
                {/if}
            </div>
        {/each}
    </div>

    <!-- Info -->
    <div class="mt-6 p-4 bg-gray-900 border border-gray-800 rounded-xl">
        <p class="text-xs text-gray-500">
            Los horarios definen cuándo el restaurante acepta reservaciones y la duración estándar de cada turno.
            El número máximo de reservaciones simultáneas determina cuántas reservaciones pueden coexistir en el mismo horario.
        </p>
    </div>
</AppLayout>

<!-- Modal -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeModal} aria-label="Cerrar"></button>

        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">{editingSlot ? 'Editar horario' : 'Nuevo horario'}</h2>
                <button onclick={closeModal} class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form onsubmit={submit} class="p-6 space-y-4">
                <div>
                    <label for="day_of_week" class="block text-sm text-gray-400 mb-1.5">Día de la semana *</label>
                    <select
                        id="day_of_week"
                        bind:value={form.day_of_week}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                    >
                        {#each (editingSlot ? daysOfWeek : availableDays) as d}
                            <option value={d.value}>{d.label}</option>
                        {/each}
                    </select>
                    {#if errors.day_of_week}
                        <p class="mt-1 text-xs text-red-400">{errors.day_of_week}</p>
                    {/if}
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="opens_at" class="block text-sm text-gray-400 mb-1.5">Apertura *</label>
                        <input
                            id="opens_at"
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
                        <label for="closes_at" class="block text-sm text-gray-400 mb-1.5">Cierre *</label>
                        <input
                            id="closes_at"
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="slot_duration" class="block text-sm text-gray-400 mb-1.5">Duración turno (min) *</label>
                        <input
                            id="slot_duration"
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
                        <label for="max_concurrent" class="block text-sm text-gray-400 mb-1.5">Máx. simultáneas *</label>
                        <input
                            id="max_concurrent"
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
                        {processing ? 'Guardando...' : (editingSlot ? 'Actualizar' : 'Crear horario')}
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
