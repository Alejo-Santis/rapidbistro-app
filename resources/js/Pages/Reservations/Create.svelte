<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { tables } = $props()

    let form = $state({
        table_id: '',
        reservation_date: '',
        starts_at: '',
        ends_at: '',
        party_size: 2,
        guest_name: '',
        guest_email: '',
        guest_phone: '',
        notes: '',
        internal_notes: '',
    })

    let errors = $state({})
    let processing = $state(false)

    const tablesByZone = $derived(() => {
        const groups = {}
        for (const t of tables) {
            const zoneName = t.zone?.name ?? 'Sin zona'
            if (!groups[zoneName]) groups[zoneName] = []
            groups[zoneName].push(t)
        }
        return groups
    })

    const selectedTable = $derived(tables.find(t => String(t.id) === String(form.table_id)))

    function submit(e) {
        e.preventDefault()
        processing = true
        router.post('/reservations', { ...form }, {
            onError: (errs) => { errors = errs },
            onFinish: () => { processing = false },
        })
    }
</script>

<AppLayout title="Nueva reservación">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="/reservations" class="hover:text-gray-300 transition-colors">Reservaciones</a>
            <span>/</span>
            <span class="text-gray-300">Nueva</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Nueva reservación</h1>
    </div>

    <form onsubmit={submit}>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Columna principal -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Datos del huésped -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Datos del huésped</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm text-gray-400 mb-1.5">Nombre completo *</label>
                            <input
                                type="text"
                                bind:value={form.guest_name}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.guest_name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                                placeholder="Nombre del huésped"
                            />
                            {#if errors.guest_name}
                                <p class="mt-1 text-xs text-red-400">{errors.guest_name}</p>
                            {/if}
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Email</label>
                            <input
                                type="email"
                                bind:value={form.guest_email}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.guest_email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                                placeholder="email@ejemplo.com"
                            />
                            {#if errors.guest_email}
                                <p class="mt-1 text-xs text-red-400">{errors.guest_email}</p>
                            {/if}
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                            <input
                                type="tel"
                                bind:value={form.guest_phone}
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                                placeholder="+1 555 0100"
                            />
                        </div>
                    </div>
                </div>

                <!-- Detalles de la reservación -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Detalles de la reservación</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Fecha *</label>
                            <input
                                type="date"
                                bind:value={form.reservation_date}
                                min={new Date().toISOString().split('T')[0]}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.reservation_date ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
                            {#if errors.reservation_date}
                                <p class="mt-1 text-xs text-red-400">{errors.reservation_date}</p>
                            {/if}
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Personas *</label>
                            <input
                                type="number"
                                bind:value={form.party_size}
                                min="1" max="100"
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.party_size ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
                            {#if errors.party_size}
                                <p class="mt-1 text-xs text-red-400">{errors.party_size}</p>
                            {/if}
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Hora de inicio *</label>
                            <input
                                type="time"
                                bind:value={form.starts_at}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.starts_at ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
                            {#if errors.starts_at}
                                <p class="mt-1 text-xs text-red-400">{errors.starts_at}</p>
                            {/if}
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Hora de fin *</label>
                            <input
                                type="time"
                                bind:value={form.ends_at}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.ends_at ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
                            {#if errors.ends_at}
                                <p class="mt-1 text-xs text-red-400">{errors.ends_at}</p>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Notas</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Notas del huésped</label>
                            <textarea
                                bind:value={form.notes}
                                rows="3"
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 resize-none"
                                placeholder="Preferencias, celebraciones, alergias..."
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Notas internas</label>
                            <textarea
                                bind:value={form.internal_notes}
                                rows="2"
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 resize-none"
                                placeholder="Solo visible para el personal..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <!-- Selección de mesa -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Asignar mesa *</h2>
                    {#if errors.table_id}
                        <p class="mb-2 text-xs text-red-400">{errors.table_id}</p>
                    {/if}
                    <div class="space-y-3">
                        {#each Object.entries(tablesByZone()) as [zone, zoneTables]}
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1.5">{zone}</p>
                                <div class="grid grid-cols-3 gap-1.5">
                                    {#each zoneTables as t}
                                        <button
                                            type="button"
                                            onclick={() => (form.table_id = String(t.id))}
                                            class="py-2 px-1 rounded-lg border text-center text-xs font-medium transition-colors
                                                {String(form.table_id) === String(t.id)
                                                    ? 'border-amber-500 bg-amber-500/10 text-amber-400'
                                                    : t.status === 'available'
                                                        ? 'border-gray-700 bg-gray-800 text-gray-300 hover:border-gray-600'
                                                        : 'border-gray-800 bg-gray-800/50 text-gray-600 cursor-not-allowed'}"
                                        >
                                            <span class="block">#{t.number}</span>
                                            <span class="block text-gray-500">{t.capacity}p</span>
                                        </button>
                                    {/each}
                                </div>
                            </div>
                        {/each}
                    </div>
                    {#if selectedTable}
                        <div class="mt-3 p-3 bg-amber-500/5 border border-amber-500/20 rounded-lg">
                            <p class="text-xs text-amber-400 font-medium">Mesa #{selectedTable.number} seleccionada</p>
                            <p class="text-xs text-gray-500">Capacidad: {selectedTable.capacity} personas · {selectedTable.zone?.name}</p>
                        </div>
                    {/if}
                </div>

                <!-- Acciones -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 space-y-3">
                    <button
                        type="submit"
                        disabled={processing}
                        class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                    >
                        {processing ? 'Guardando...' : 'Crear reservación'}
                    </button>
                    <a
                        href="/reservations"
                        class="block w-full py-2.5 text-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors"
                    >
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</AppLayout>
