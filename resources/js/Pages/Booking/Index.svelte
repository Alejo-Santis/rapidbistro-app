<script>
    import GuestLayout from '../../Layouts/GuestLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { restaurant, minDate, maxDate } = $props()

    // Paso actual: 1 = fecha/personas, 2 = horario, 3 = datos personales
    let step        = $state(1)
    let loading     = $state(false)
    let processing  = $state(false)
    let errors      = $state({})

    // Datos del formulario
    let date        = $state(minDate)
    let partySize   = $state(2)
    let slots       = $state([])
    let selectedSlot = $state(null)
    let closed      = $state(false)
    let noTables    = $state(false)

    let guestName   = $state('')
    let guestEmail  = $state('')
    let guestPhone  = $state('')
    let notes       = $state('')

    const checkAvailability = async () => {
        if (!date || !partySize) return
        loading  = true
        closed   = false
        noTables = false
        slots    = []
        selectedSlot = null

        try {
            const res = await fetch(`/reservar/disponibilidad?date=${date}&party_size=${partySize}`)
            const data = await res.json()
            closed   = data.closed ?? false
            noTables = data.no_tables ?? false
            slots    = data.available ?? []
        } finally {
            loading = false
        }
    }

    const goToStep2 = async () => {
        await checkAvailability()
        step = 2
    }

    const selectSlot = (slot) => {
        selectedSlot = slot
        step = 3
    }

    const goBack = () => {
        if (step === 3) { step = 2; return }
        if (step === 2) { step = 1; selectedSlot = null }
    }

    const submit = (e) => {
        e.preventDefault()
        processing = true
        errors = {}

        router.post('/reservar', {
            reservation_date: date,
            starts_at:        selectedSlot.starts_at,
            party_size:       partySize,
            guest_name:       guestName,
            guest_email:      guestEmail,
            guest_phone:      guestPhone,
            notes,
        }, {
            onError:  (errs) => { errors = errs; processing = false },
            onFinish: () => { processing = false },
        })
    }

    const dateLabel = $derived(() => {
        if (!date) return ''
        return new Date(date + 'T12:00:00').toLocaleDateString('es', {
            weekday: 'long', day: 'numeric', month: 'long',
        })
    })
</script>

<GuestLayout {restaurant} title="Hacer una reservación">

    <!-- Progreso -->
    <div class="flex items-center gap-2 mb-8">
        {#each [1, 2, 3] as s}
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors
                    {step >= s ? 'bg-amber-500 text-gray-900' : 'bg-gray-800 text-gray-500'}">
                    {s}
                </div>
                {#if s < 3}
                    <div class="flex-1 h-px w-8 {step > s ? 'bg-amber-500' : 'bg-gray-800'}"></div>
                {/if}
            </div>
        {/each}
        <div class="ml-2 text-xs text-gray-500">
            {step === 1 ? 'Fecha y personas' : step === 2 ? 'Elige tu horario' : 'Tus datos'}
        </div>
    </div>

    <!-- PASO 1: Fecha y personas -->
    {#if step === 1}
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Haz tu reservación</h1>
                <p class="text-gray-400 text-sm mt-1">Selecciona la fecha y el número de personas.</p>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
                <div>
                    <label for="date" class="block text-sm text-gray-400 mb-2">Fecha *</label>
                    <input
                        id="date"
                        type="date"
                        bind:value={date}
                        min={minDate}
                        max={maxDate}
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm focus:outline-none focus:border-amber-500"
                    />
                </div>

                <div>
                    <label for="party" class="block text-sm text-gray-400 mb-2">
                        Número de personas: <span class="text-amber-400 font-semibold">{partySize}</span>
                    </label>
                    <input
                        id="party"
                        type="range"
                        bind:value={partySize}
                        min="1"
                        max="20"
                        class="w-full accent-amber-500"
                    />
                    <div class="flex justify-between text-xs text-gray-600 mt-1">
                        <span>1</span><span>20</span>
                    </div>
                </div>
            </div>

            <button
                onclick={goToStep2}
                disabled={!date || loading}
                class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/40 text-gray-900 font-bold rounded-xl text-sm transition-colors"
            >
                {loading ? 'Verificando disponibilidad...' : 'Continuar →'}
            </button>
        </div>
    {/if}

    <!-- PASO 2: Selección de horario -->
    {#if step === 2}
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <button onclick={goBack} class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h2 class="text-xl font-bold text-white">Elige tu horario</h2>
                    <p class="text-gray-400 text-sm capitalize">{dateLabel()} · {partySize} persona{partySize > 1 ? 's' : ''}</p>
                </div>
            </div>

            {#if loading}
                <div class="text-center py-12 text-gray-500">
                    <div class="w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-sm">Verificando disponibilidad...</p>
                </div>
            {:else if closed}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 text-center">
                    <p class="text-gray-400 text-sm mb-1">El restaurante no tiene horario de atención ese día.</p>
                    <button onclick={goBack} class="mt-4 text-amber-400 text-sm hover:text-amber-300">← Elegir otra fecha</button>
                </div>
            {:else if noTables || slots.length === 0}
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 text-center">
                    <p class="text-white font-semibold mb-1">No hay disponibilidad</p>
                    <p class="text-gray-400 text-sm mb-4">No tenemos mesas disponibles para {partySize} persona{partySize > 1 ? 's' : ''} ese día.</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button onclick={goBack} class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors">
                            ← Otra fecha
                        </button>
                        <a
                            href="/reservar/lista-espera?date={date}&party_size={partySize}"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                        >
                            Unirme a lista de espera
                        </a>
                    </div>
                </div>
            {:else}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    {#each slots as slot}
                        <button
                            onclick={() => selectSlot(slot)}
                            class="bg-gray-900 border border-gray-800 hover:border-amber-500 rounded-xl p-4 text-left transition-colors group"
                        >
                            <p class="text-white font-semibold text-lg group-hover:text-amber-400 transition-colors">
                                {slot.starts_at}
                            </p>
                            {#if slot.service}
                                <p class="text-xs text-amber-400/70 mt-0.5">{slot.service}</p>
                            {/if}
                            <p class="text-xs text-gray-500 mt-1">hasta {slot.ends_at}</p>
                        </button>
                    {/each}
                </div>

                <a
                    href="/reservar/lista-espera?date={date}&party_size={partySize}"
                    class="block text-center text-xs text-gray-600 hover:text-gray-400 transition-colors mt-2"
                >
                    ¿No encuentras el horario que buscas? Únete a la lista de espera →
                </a>
            {/if}
        </div>
    {/if}

    <!-- PASO 3: Datos personales -->
    {#if step === 3}
        <form onsubmit={submit} class="space-y-6">
            <div class="flex items-center gap-3">
                <button type="button" onclick={goBack} class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div>
                    <h2 class="text-xl font-bold text-white">Tus datos</h2>
                    <p class="text-gray-400 text-sm capitalize">
                        {dateLabel()} · {selectedSlot?.starts_at} · {partySize} persona{partySize > 1 ? 's' : ''}
                    </p>
                </div>
            </div>

            <!-- Resumen de la reservación -->
            <div class="bg-gray-900 border border-amber-500/20 rounded-xl px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 bg-amber-500/10 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-sm">
                    <p class="text-white font-semibold capitalize">{dateLabel()}</p>
                    <p class="text-gray-400">{selectedSlot?.starts_at}–{selectedSlot?.ends_at} · {partySize} persona{partySize > 1 ? 's' : ''}</p>
                </div>
            </div>

            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
                <div>
                    <label for="guest_name" class="block text-sm text-gray-400 mb-1.5">Nombre completo *</label>
                    <input
                        id="guest_name"
                        type="text"
                        bind:value={guestName}
                        placeholder="Tu nombre"
                        class="w-full px-4 py-3 bg-gray-800 border rounded-xl text-white text-sm focus:outline-none
                            {errors.guest_name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                    />
                    {#if errors.guest_name}
                        <p class="mt-1 text-xs text-red-400">{errors.guest_name}</p>
                    {/if}
                </div>

                <div>
                    <label for="guest_email" class="block text-sm text-gray-400 mb-1.5">Correo electrónico *</label>
                    <input
                        id="guest_email"
                        type="email"
                        bind:value={guestEmail}
                        placeholder="tu@email.com"
                        class="w-full px-4 py-3 bg-gray-800 border rounded-xl text-white text-sm focus:outline-none
                            {errors.guest_email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                    />
                    {#if errors.guest_email}
                        <p class="mt-1 text-xs text-red-400">{errors.guest_email}</p>
                    {/if}
                </div>

                <div>
                    <label for="guest_phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                    <input
                        id="guest_phone"
                        type="tel"
                        bind:value={guestPhone}
                        placeholder="+1 555 0000"
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl text-white text-sm focus:outline-none"
                    />
                </div>

                <div>
                    <label for="notes" class="block text-sm text-gray-400 mb-1.5">Notas o peticiones especiales</label>
                    <textarea
                        id="notes"
                        bind:value={notes}
                        rows="3"
                        placeholder="Alergias, celebraciones, preferencias de mesa..."
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl text-white text-sm focus:outline-none resize-none"
                    ></textarea>
                </div>
            </div>

            {#if errors.starts_at}
                <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3">
                    <p class="text-sm text-red-400">{errors.starts_at}</p>
                </div>
            {/if}

            <button
                type="submit"
                disabled={processing || !guestName || !guestEmail}
                class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/40 text-gray-900 font-bold rounded-xl text-sm transition-colors"
            >
                {processing ? 'Confirmando reservación...' : 'Confirmar reservación'}
            </button>

            {#if restaurant?.policy}
                <p class="text-xs text-gray-600 text-center leading-relaxed">{restaurant.policy}</p>
            {/if}
        </form>
    {/if}

</GuestLayout>
