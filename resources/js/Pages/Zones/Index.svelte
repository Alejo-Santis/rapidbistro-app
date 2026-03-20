<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { zones, locationOptions } = $props()

    let modalOpen = $state(false)
    let editingZone = $state(null)

    let form = $state({
        name: '',
        description: '',
        location: 'indoor',
        sort_order: 0,
        is_active: true,
    })
    let errors = $state({})
    let processing = $state(false)

    function openCreate() {
        editingZone = null
        form = { name: '', description: '', location: 'indoor', sort_order: 0, is_active: true }
        errors = {}
        modalOpen = true
    }

    function openEdit(zone) {
        editingZone = zone
        form = {
            name: zone.name,
            description: zone.description ?? '',
            location: zone.location,
            sort_order: zone.sort_order ?? 0,
            is_active: zone.is_active,
        }
        errors = {}
        modalOpen = true
    }

    function closeModal() {
        modalOpen = false
        editingZone = null
        form = { name: '', description: '', location: 'indoor', sort_order: 0, is_active: true }
        errors = {}
    }

    function submit(e) {
        e.preventDefault()
        processing = true
        if (editingZone) {
            router.put(`/zones/${editingZone.uuid}`, { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        } else {
            router.post('/zones', { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        }
    }

    function deleteZone(zone) {
        if (confirm(`¿Eliminar la zona "${zone.name}"? Esta acción no se puede deshacer.`)) {
            router.delete(`/zones/${zone.uuid}`)
        }
    }

    const locationColors = {
        indoor:  'bg-blue-500/10 text-blue-400',
        outdoor: 'bg-green-500/10 text-green-400',
        rooftop: 'bg-purple-500/10 text-purple-400',
        bar:     'bg-orange-500/10 text-orange-400',
        private: 'bg-pink-500/10 text-pink-400',
        lounge:  'bg-cyan-500/10 text-cyan-400',
    }
</script>

<AppLayout title="Zonas">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Zonas</h1>
            <p class="text-gray-400 text-sm mt-1">{zones.length} zonas configuradas</p>
        </div>
        <button
            onclick={openCreate}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nueva zona
        </button>
    </div>

    <!-- Grid de zonas -->
    {#if zones.length === 0}
        <div class="text-center py-20 text-gray-500">
            <svg class="w-14 h-14 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
            </svg>
            <p class="text-sm">No hay zonas configuradas</p>
            <button onclick={openCreate} class="mt-3 text-amber-400 hover:text-amber-300 text-sm transition-colors">
                Crear la primera zona
            </button>
        </div>
    {:else}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {#each zones as zone}
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 hover:border-gray-700 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-white">{zone.name}</h3>
                            {#if zone.description}
                                <p class="text-xs text-gray-500 mt-0.5">{zone.description}</p>
                            {/if}
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {locationColors[zone.location] ?? 'bg-gray-700 text-gray-400'}">
                            {zone.location_label}
                        </span>
                    </div>

                    <div class="flex items-center gap-4 text-sm text-gray-400 mb-4">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            {zone.tables_count} mesas
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {zone.is_active ? 'bg-green-400' : 'bg-gray-600'}"></span>
                            {zone.is_active ? 'Activa' : 'Inactiva'}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button
                            onclick={() => openEdit(zone)}
                            class="flex-1 py-1.5 text-xs text-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg transition-colors"
                        >
                            Editar
                        </button>
                        <button
                            onclick={() => deleteZone(zone)}
                            class="flex-1 py-1.5 text-xs text-center bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            {/each}
        </div>
    {/if}
</AppLayout>

<!-- Modal -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button
            class="absolute inset-0 bg-black/60"
            onclick={closeModal}
            aria-label="Cerrar"
        ></button>

        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">
                    {editingZone ? 'Editar zona' : 'Nueva zona'}
                </h2>
                <button onclick={closeModal} class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form onsubmit={submit} class="p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nombre *</label>
                    <input
                        type="text"
                        bind:value={form.name}
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                            {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                        placeholder="Ej. Salón Principal"
                    />
                    {#if errors.name}
                        <p class="mt-1 text-xs text-red-400">{errors.name}</p>
                    {/if}
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Descripción</label>
                    <textarea
                        bind:value={form.description}
                        rows="2"
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 resize-none"
                        placeholder="Descripción opcional"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Tipo de área *</label>
                        <select
                            bind:value={form.location}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        >
                            {#each locationOptions as opt}
                                <option value={opt.value}>{opt.label}</option>
                            {/each}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Orden</label>
                        <input
                            type="number"
                            bind:value={form.sort_order}
                            min="0"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        />
                    </div>
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        bind:checked={form.is_active}
                        class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-gray-900"
                    />
                    <span class="text-sm text-gray-300">Zona activa</span>
                </label>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                    >
                        {processing ? 'Guardando...' : (editingZone ? 'Actualizar' : 'Crear zona')}
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
