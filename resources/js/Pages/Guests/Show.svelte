<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { guest, reservations } = $props()

    let editing    = $state(false)
    let processing = $state(false)
    let errors     = $state({})

    let form = $state({ ...guest })

    const statusColors = {
        pending:   'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        seated:    'bg-green-500/10 text-green-400 border-green-500/20',
        completed: 'bg-gray-500/10 text-gray-400 border-gray-500/20',
        cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
        no_show:   'bg-orange-500/10 text-orange-400 border-orange-500/20',
    }

    const visitCount  = reservations.filter(r => ['completed','seated','confirmed'].includes(r.status)).length
    const noShowCount = reservations.filter(r => r.status === 'no_show').length

    const save = (e) => {
        e.preventDefault()
        processing = true
        router.put(`/guests/${guest.uuid}`, { ...form }, {
            onError:   (errs) => { errors = errs },
            onSuccess: () => { editing = false },
            onFinish:  () => { processing = false },
        })
    }

    const destroy = () => {
        if (confirm(`¿Eliminar el perfil de "${guest.name}"? Esto no elimina sus reservaciones.`)) {
            router.delete(`/guests/${guest.uuid}`)
        }
    }
</script>

<AppLayout title="Perfil de cliente">

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="/guests" class="hover:text-gray-300 transition-colors">Clientes</a>
        <span>/</span>
        <span class="text-gray-300">{guest.name}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna izquierda: perfil CRM -->
        <div class="space-y-4">

            <!-- Cabecera del perfil -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-lg font-bold text-white">
                            {guest.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-lg font-bold text-white">{guest.name}</h1>
                                {#if guest.is_vip}
                                    <span class="text-[10px] px-1.5 py-0.5 bg-amber-500/15 text-amber-400 border border-amber-500/20 rounded-full">VIP</span>
                                {/if}
                            </div>
                            {#if guest.email}
                                <p class="text-xs text-gray-500">{guest.email}</p>
                            {/if}
                        </div>
                    </div>
                    <button onclick={() => { editing = !editing; form = { ...guest } }}
                        class="text-xs text-amber-400 hover:text-amber-300 transition-colors">
                        {editing ? 'Cancelar' : 'Editar'}
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-2 py-3 border-y border-gray-800 mb-4">
                    <div class="text-center">
                        <p class="text-xl font-bold {visitCount >= 10 ? 'text-amber-400' : visitCount >= 5 ? 'text-emerald-400' : 'text-white'}">{visitCount}</p>
                        <p class="text-[10px] text-gray-600">visitas</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xl font-bold {noShowCount > 0 ? 'text-orange-400' : 'text-gray-600'}">{noShowCount}</p>
                        <p class="text-[10px] text-gray-600">no-shows</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xl font-bold text-white">{reservations.length}</p>
                        <p class="text-[10px] text-gray-600">total</p>
                    </div>
                </div>

                {#if !editing}
                    <!-- Vista de datos -->
                    <div class="space-y-2.5 text-xs">
                        {#if guest.phone}
                            <div class="flex justify-between"><span class="text-gray-500">Teléfono</span><span class="text-gray-300">{guest.phone}</span></div>
                        {/if}
                        {#if guest.birthday}
                            <div class="flex justify-between"><span class="text-gray-500">Cumpleaños</span><span class="text-gray-300">{new Date(guest.birthday + 'T12:00:00').toLocaleDateString('es', {day:'2-digit',month:'long'})}</span></div>
                        {/if}
                        {#if guest.anniversary}
                            <div class="flex justify-between"><span class="text-gray-500">Aniversario</span><span class="text-gray-300">{new Date(guest.anniversary + 'T12:00:00').toLocaleDateString('es', {day:'2-digit',month:'long'})}</span></div>
                        {/if}
                        {#if guest.allergies}
                            <div><p class="text-gray-500 mb-1">Alergias</p><p class="text-red-400/80 bg-red-500/5 border border-red-500/10 rounded-lg px-2.5 py-1.5">{guest.allergies}</p></div>
                        {/if}
                        {#if guest.preferences}
                            <div><p class="text-gray-500 mb-1">Preferencias</p><p class="text-gray-300 bg-gray-800 rounded-lg px-2.5 py-1.5">{guest.preferences}</p></div>
                        {/if}
                        {#if guest.staff_notes}
                            <div><p class="text-gray-500 mb-1">Notas del staff</p><p class="text-amber-400/70 italic bg-amber-500/5 border border-amber-500/10 rounded-lg px-2.5 py-1.5">{guest.staff_notes}</p></div>
                        {/if}
                    </div>
                {:else}
                    <!-- Formulario de edición -->
                    <form onsubmit={save} class="space-y-3">
                        <div>
                            <label for="e_name" class="block text-xs text-gray-500 mb-1">Nombre *</label>
                            <input id="e_name" type="text" bind:value={form.name}
                                class="w-full px-3 py-2 bg-gray-800 border rounded-lg text-white text-xs focus:outline-none {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                        </div>
                        <div>
                            <label for="e_email" class="block text-xs text-gray-500 mb-1">Email</label>
                            <input id="e_email" type="email" bind:value={form.email}
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none" />
                            {#if errors.email}<p class="mt-1 text-xs text-red-400">{errors.email}</p>{/if}
                        </div>
                        <div>
                            <label for="e_phone" class="block text-xs text-gray-500 mb-1">Teléfono</label>
                            <input id="e_phone" type="tel" bind:value={form.phone}
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="e_bday" class="block text-xs text-gray-500 mb-1">Cumpleaños</label>
                                <input id="e_bday" type="date" bind:value={form.birthday}
                                    class="w-full px-2 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none" />
                            </div>
                            <div>
                                <label for="e_ann" class="block text-xs text-gray-500 mb-1">Aniversario</label>
                                <input id="e_ann" type="date" bind:value={form.anniversary}
                                    class="w-full px-2 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none" />
                            </div>
                        </div>
                        <div>
                            <label for="e_allergy" class="block text-xs text-gray-500 mb-1">Alergias</label>
                            <input id="e_allergy" type="text" bind:value={form.allergies}
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none" />
                        </div>
                        <div>
                            <label for="e_pref" class="block text-xs text-gray-500 mb-1">Preferencias</label>
                            <textarea id="e_pref" bind:value={form.preferences} rows="2"
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label for="e_notes" class="block text-xs text-gray-500 mb-1">Notas del staff</label>
                            <textarea id="e_notes" bind:value={form.staff_notes} rows="2"
                                class="w-full px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-xs focus:outline-none resize-none"></textarea>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" bind:checked={form.is_vip} class="accent-amber-500" />
                            <span class="text-xs text-gray-400">Cliente VIP</span>
                        </label>
                        <button type="submit" disabled={processing}
                            class="w-full py-2 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-xs transition-colors">
                            {processing ? 'Guardando...' : 'Guardar cambios'}
                        </button>
                    </form>
                {/if}

                <button onclick={destroy} class="mt-4 w-full text-xs text-red-400/60 hover:text-red-400 transition-colors text-center">
                    Eliminar perfil
                </button>
            </div>

        </div>

        <!-- Columna derecha: historial de reservaciones -->
        <div class="lg:col-span-2">
            <h2 class="text-base font-semibold text-white mb-4">Historial de reservaciones ({reservations.length})</h2>

            {#if reservations.length === 0}
                <div class="bg-gray-900 border border-gray-800 rounded-xl py-12 text-center text-gray-500">
                    <p class="text-sm">Sin reservaciones registradas con este email.</p>
                </div>
            {:else}
                <div class="space-y-2">
                    {#each reservations as r}
                        <a href="/reservations/{r.uuid}/edit"
                            class="flex items-center gap-4 bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-4 py-3.5 transition-colors group">
                            <div class="shrink-0">
                                <p class="text-sm font-semibold text-white group-hover:text-amber-400 transition-colors">{r.reservation_date}</p>
                                <p class="text-xs text-gray-500 font-mono">{r.starts_at}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-400">
                                    {r.party_size} personas
                                    {#if r.table_number} · Mesa {r.table_number}{/if}
                                    {#if r.zone_name} · {r.zone_name}{/if}
                                </p>
                                {#if r.notes}
                                    <p class="text-xs text-gray-600 italic truncate">"{r.notes}"</p>
                                {/if}
                            </div>
                            <div class="shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {statusColors[r.status] ?? ''}">
                                    {r.status_label}
                                </span>
                            </div>
                            <span class="text-xs font-mono text-gray-600">{r.confirmation_code}</span>
                        </a>
                    {/each}
                </div>
            {/if}
        </div>

    </div>

</AppLayout>
