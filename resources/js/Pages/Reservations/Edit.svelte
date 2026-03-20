<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { reservation, tables } = $props()

    let form = $state({
        table_id: String(reservation.table_id ?? ''),
        reservation_date: reservation.reservation_date ?? '',
        starts_at: reservation.starts_at ?? '',
        ends_at: reservation.ends_at ?? '',
        party_size: reservation.party_size ?? 2,
        status: reservation.status ?? 'pending',
        guest_name: reservation.guest_name ?? '',
        guest_email: reservation.guest_email ?? '',
        guest_phone: reservation.guest_phone ?? '',
        notes: reservation.notes ?? '',
        internal_notes: reservation.internal_notes ?? '',
        cancellation_reason: reservation.cancellation_reason ?? '',
    })

    let errors = $state({})
    let processing = $state(false)

    const statusOptions = [
        { value: 'pending', label: 'Pendiente' },
        { value: 'confirmed', label: 'Confirmada' },
        { value: 'seated', label: 'En mesa' },
        { value: 'completed', label: 'Completada' },
        { value: 'cancelled', label: 'Cancelada' },
        { value: 'no_show', label: 'No se presentó' },
    ]

    const statusColors = {
        pending:   'text-yellow-400',
        confirmed: 'text-blue-400',
        seated:    'text-green-400',
        completed: 'text-gray-400',
        cancelled: 'text-red-400',
        no_show:   'text-orange-400',
    }

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
    const showCancellationReason = $derived(form.status === 'cancelled')

    function submit(e) {
        e.preventDefault()
        processing = true
        router.put(`/reservations/${reservation.uuid}`, { ...form }, {
            onError: (errs) => { errors = errs },
            onFinish: () => { processing = false },
        })
    }
</script>

<AppLayout title="Editar reservación">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
            <a href="/reservations" class="hover:text-gray-300 transition-colors">Reservaciones</a>
            <span>/</span>
            <span class="text-gray-300">Editar</span>
        </div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-white">Reservación</h1>
            <span class="font-mono text-amber-400 text-lg">#{reservation.confirmation_code}</span>
        </div>
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
                            />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                            <input
                                type="tel"
                                bind:value={form.guest_phone}
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Detalles -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Detalles de la reservación</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Fecha *</label>
                            <input
                                type="date"
                                bind:value={form.reservation_date}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.reservation_date ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
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
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Hora de inicio *</label>
                            <input
                                type="time"
                                bind:value={form.starts_at}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.starts_at ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Hora de fin *</label>
                            <input
                                type="time"
                                bind:value={form.ends_at}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.ends_at ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
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
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5">Notas internas</label>
                            <textarea
                                bind:value={form.internal_notes}
                                rows="2"
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 resize-none"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Historial de estados -->
                {#if reservation.status_logs?.length > 0}
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                        <h2 class="font-semibold text-white mb-4">Historial de cambios</h2>
                        <div class="space-y-3">
                            {#each reservation.status_logs as log}
                                <div class="flex items-start gap-3 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-gray-600 mt-1.5 flex-shrink-0"></div>
                                    <div>
                                        <p class="text-gray-300">
                                            <span class={statusColors[log.previous_status] ?? 'text-gray-400'}>{log.previous_status}</span>
                                            <span class="text-gray-600 mx-1">→</span>
                                            <span class={statusColors[log.new_status] ?? 'text-gray-400'}>{log.new_status}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {log.user_name} · {log.created_at}
                                            {#if log.reason}
                                                · {log.reason}
                                            {/if}
                                        </p>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <!-- Estado -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Estado</h2>
                    <select
                        bind:value={form.status}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm focus:outline-none focus:border-amber-500
                            {statusColors[form.status] ?? 'text-white'}"
                    >
                        {#each statusOptions as opt}
                            <option value={opt.value}>{opt.label}</option>
                        {/each}
                    </select>
                    {#if showCancellationReason}
                        <div class="mt-3">
                            <label class="block text-sm text-gray-400 mb-1.5">Razón de cancelación</label>
                            <textarea
                                bind:value={form.cancellation_reason}
                                rows="2"
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none resize-none
                                    {errors.cancellation_reason ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                                placeholder="Motivo de la cancelación..."
                            ></textarea>
                            {#if errors.cancellation_reason}
                                <p class="mt-1 text-xs text-red-400">{errors.cancellation_reason}</p>
                            {/if}
                        </div>
                    {/if}
                </div>

                <!-- Mesa -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Mesa</h2>
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
                                                    : 'border-gray-700 bg-gray-800 text-gray-300 hover:border-gray-600'}"
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
                        <div class="mt-3 p-2 bg-amber-500/5 border border-amber-500/20 rounded-lg">
                            <p class="text-xs text-amber-400">Mesa #{selectedTable.number} · {selectedTable.zone?.name}</p>
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
                        {processing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                    <a
                        href="/reservations"
                        class="block w-full py-2.5 text-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors"
                    >
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </form>
</AppLayout>
