<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { tables, zones, statusOptions } = $props()

    let modalOpen = $state(false)
    let editingTable = $state(null)
    let filterZone = $state('')
    let filterStatus = $state('')

    let form = $state({
        zone_id: '',
        number: '',
        capacity: 4,
        min_capacity: 1,
        status: 'available',
    })
    let errors = $state({})
    let processing = $state(false)

    const filteredTables = $derived(
        tables.filter(t => {
            if (filterZone && String(t.zone_id) !== String(filterZone)) return false
            if (filterStatus && t.status !== filterStatus) return false
            return true
        })
    )

    const statusColors = {
        available:   'bg-green-500/10 text-green-400 border-green-500/20',
        reserved:    'bg-blue-500/10 text-blue-400 border-blue-500/20',
        occupied:    'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        maintenance: 'bg-orange-500/10 text-orange-400 border-orange-500/20',
        unavailable: 'bg-red-500/10 text-red-400 border-red-500/20',
    }

    function openCreate() {
        editingTable = null
        form = {
            zone_id: zones[0]?.id ? String(zones[0].id) : '',
            number: '',
            capacity: 4,
            min_capacity: 1,
            status: 'available',
        }
        errors = {}
        modalOpen = true
    }

    function openEdit(table) {
        editingTable = table
        form = {
            zone_id: String(table.zone_id),
            number: table.number,
            capacity: table.capacity,
            min_capacity: table.min_capacity ?? 1,
            status: table.status,
        }
        errors = {}
        modalOpen = true
    }

    function closeModal() {
        modalOpen = false
        editingTable = null
        form = { zone_id: '', number: '', capacity: 4, min_capacity: 1, status: 'available' }
        errors = {}
    }

    function submit(e) {
        e.preventDefault()
        processing = true
        if (editingTable) {
            router.put(`/tables/${editingTable.uuid}`, { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        } else {
            router.post('/tables', { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        }
    }

    function deleteTable(table) {
        if (confirm(`¿Eliminar la mesa #${table.number}?`)) {
            router.delete(`/tables/${table.uuid}`)
        }
    }
</script>

<AppLayout title="Mesas">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Mesas</h1>
            <p class="text-gray-400 text-sm mt-1">{tables.length} mesas en total</p>
        </div>
        <button
            onclick={openCreate}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nueva mesa
        </button>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-3 mb-5">
        <select
            bind:value={filterZone}
            class="px-3 py-2 bg-gray-900 border border-gray-800 rounded-lg text-sm text-gray-300 focus:outline-none focus:border-amber-500"
        >
            <option value="">Todas las zonas</option>
            {#each zones as z}
                <option value={z.id}>{z.name}</option>
            {/each}
        </select>
        <select
            bind:value={filterStatus}
            class="px-3 py-2 bg-gray-900 border border-gray-800 rounded-lg text-sm text-gray-300 focus:outline-none focus:border-amber-500"
        >
            <option value="">Todos los estados</option>
            {#each statusOptions as opt}
                <option value={opt.value}>{opt.label}</option>
            {/each}
        </select>
        <span class="flex items-center text-xs text-gray-500">
            {filteredTables.length} resultados
        </span>
    </div>

    <!-- Tabla de mesas -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        {#if filteredTables.length === 0}
            <div class="text-center py-16 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <p class="text-sm">No hay mesas que mostrar</p>
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Mesa</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Zona</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Capacidad</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Estado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        {#each filteredTables as t}
                            <tr class="hover:bg-gray-800/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-white">#{t.number}</span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-300">{t.zone_name ?? '—'}</td>
                                <td class="px-5 py-3.5 text-gray-300">
                                    {t.min_capacity ?? 1}–{t.capacity} personas
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {statusColors[t.status] ?? ''}">
                                        {t.status_label}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <button
                                            onclick={() => openEdit(t)}
                                            class="text-xs text-amber-400 hover:text-amber-300 transition-colors"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            onclick={() => deleteTable(t)}
                                            class="text-xs text-red-400 hover:text-red-300 transition-colors"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
</AppLayout>

<!-- Modal -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeModal} aria-label="Cerrar"></button>

        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">{editingTable ? 'Editar mesa' : 'Nueva mesa'}</h2>
                <button onclick={closeModal} class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form onsubmit={submit} class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Número de mesa *</label>
                        <input
                            type="text"
                            bind:value={form.number}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {errors.number ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            placeholder="Ej. 1, A1, VIP"
                        />
                        {#if errors.number}
                            <p class="mt-1 text-xs text-red-400">{errors.number}</p>
                        {/if}
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Zona *</label>
                        <select
                            bind:value={form.zone_id}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        >
                            {#each zones as z}
                                <option value={String(z.id)}>{z.name}</option>
                            {/each}
                        </select>
                        {#if errors.zone_id}
                            <p class="mt-1 text-xs text-red-400">{errors.zone_id}</p>
                        {/if}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Capacidad máx. *</label>
                        <input
                            type="number"
                            bind:value={form.capacity}
                            min="1" max="50"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Capacidad mín.</label>
                        <input
                            type="number"
                            bind:value={form.min_capacity}
                            min="1"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Estado *</label>
                    <select
                        bind:value={form.status}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                    >
                        {#each statusOptions as opt}
                            <option value={opt.value}>{opt.label}</option>
                        {/each}
                    </select>
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                    >
                        {processing ? 'Guardando...' : (editingTable ? 'Actualizar' : 'Crear mesa')}
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
