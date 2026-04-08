<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'
    import { untrack } from 'svelte'

    let { guests, filters } = $props()

    let search     = $state(untrack(() => filters.search ?? ''))
    let vipOnly    = $state(untrack(() => filters.vip === 'true' || filters.vip === true))
    let modalOpen  = $state(false)
    let processing = $state(false)
    let errors     = $state({})

    const defaultForm = () => ({
        name: '', email: '', phone: '',
        birthday: '', anniversary: '',
        allergies: '', preferences: '', staff_notes: '',
        is_vip: false,
    })
    let form = $state(defaultForm())

    const applyFilters = () => {
        router.get('/guests', {
            search: search || undefined,
            vip:    vipOnly || undefined,
        }, { preserveState: true, replace: true })
    }

    const openCreate = () => {
        form = defaultForm()
        errors = {}
        modalOpen = true
    }

    const closeModal = () => {
        modalOpen = false
        form = defaultForm()
        errors = {}
    }

    const submit = (e) => {
        e.preventDefault()
        processing = true
        router.post('/guests', { ...form }, {
            onError:   (errs) => { errors = errs },
            onSuccess: () => closeModal(),
            onFinish:  () => { processing = false },
        })
    }

    const visitColor = (count) => {
        if (count >= 10) return 'text-amber-400'
        if (count >= 5)  return 'text-emerald-400'
        return 'text-gray-400'
    }
</script>

<AppLayout title="Clientes">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Clientes</h1>
            <p class="text-gray-400 text-sm mt-1">{guests.meta?.total ?? 0} perfiles registrados</p>
        </div>
        <button
            onclick={openCreate}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo cliente
        </button>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-3 mb-5 items-center">
        <input
            type="text"
            bind:value={search}
            onkeydown={(e) => e.key === 'Enter' && applyFilters()}
            placeholder="Buscar por nombre o email..."
            class="flex-1 min-w-[200px] px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm placeholder-gray-500 focus:outline-none focus:border-amber-500"
        />
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" bind:checked={vipOnly} onchange={applyFilters} class="accent-amber-500" />
            <span class="text-sm text-gray-400">Solo VIP</span>
        </label>
        <button onclick={applyFilters} class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-medium rounded-lg text-sm transition-colors">
            Buscar
        </button>
    </div>

    <!-- Grid de clientes -->
    {#if guests.data.length === 0}
        <div class="text-center py-20 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="text-sm">No hay perfiles de clientes aún</p>
            <button onclick={openCreate} class="mt-3 text-xs text-amber-400 hover:text-amber-300">+ Crear el primero</button>
        </div>
    {:else}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {#each guests.data as g}
                <a
                    href="/guests/{g.uuid}"
                    class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl p-5 block transition-colors group"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-sm font-bold text-white shrink-0">
                                {g.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p class="text-sm font-semibold text-white group-hover:text-amber-400 transition-colors">{g.name}</p>
                                    {#if g.is_vip}
                                        <span class="text-[10px] px-1.5 py-0.5 bg-amber-500/15 text-amber-400 border border-amber-500/20 rounded-full font-medium">VIP</span>
                                    {/if}
                                </div>
                                {#if g.email}
                                    <p class="text-xs text-gray-500">{g.email}</p>
                                {/if}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 text-xs">
                        <div class="text-center">
                            <p class="font-bold {visitColor(g.visit_count)}">{g.visit_count}</p>
                            <p class="text-gray-600">visitas</p>
                        </div>
                        {#if g.no_show_count > 0}
                            <div class="text-center">
                                <p class="font-bold text-orange-400">{g.no_show_count}</p>
                                <p class="text-gray-600">no-shows</p>
                            </div>
                        {/if}
                        {#if g.last_visit}
                            <div class="ml-auto text-right">
                                <p class="text-gray-500">Última visita</p>
                                <p class="text-gray-400">{new Date(g.last_visit + 'T12:00:00').toLocaleDateString('es', { day: '2-digit', month: 'short' })}</p>
                            </div>
                        {/if}
                    </div>

                    {#if g.allergies}
                        <div class="mt-3 pt-3 border-t border-gray-800">
                            <p class="text-xs text-red-400/70 truncate">⚠ {g.allergies}</p>
                        </div>
                    {/if}
                </a>
            {/each}
        </div>

        <!-- Paginación -->
        {#if guests.meta?.last_page > 1}
            <div class="flex justify-center gap-1 mt-6">
                {#each guests.meta.links as link}
                    {#if link.url}
                        <a href={link.url} class="px-3 py-1.5 text-xs rounded-lg {link.active ? 'bg-amber-500 text-gray-900 font-semibold' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'}">
                            {@html link.label}
                        </a>
                    {:else}
                        <span class="px-3 py-1.5 text-xs text-gray-600">{@html link.label}</span>
                    {/if}
                {/each}
            </div>
        {/if}
    {/if}

</AppLayout>

<!-- Modal nuevo cliente -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeModal} aria-label="Cerrar"></button>
        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800 sticky top-0 bg-gray-900">
                <h2 class="font-semibold text-white">Nuevo cliente</h2>
                <button onclick={closeModal} aria-label="Cerrar modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form onsubmit={submit} class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label for="g_name" class="block text-sm text-gray-400 mb-1.5">Nombre *</label>
                        <input id="g_name" type="text" bind:value={form.name}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                        {#if errors.name}<p class="mt-1 text-xs text-red-400">{errors.name}</p>{/if}
                    </div>
                    <div>
                        <label for="g_email" class="block text-sm text-gray-400 mb-1.5">Email</label>
                        <input id="g_email" type="email" bind:value={form.email}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none {errors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                        {#if errors.email}<p class="mt-1 text-xs text-red-400">{errors.email}</p>{/if}
                    </div>
                    <div>
                        <label for="g_phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                        <input id="g_phone" type="tel" bind:value={form.phone}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                    </div>
                    <div>
                        <label for="g_bday" class="block text-sm text-gray-400 mb-1.5">Cumpleaños</label>
                        <input id="g_bday" type="date" bind:value={form.birthday}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                    </div>
                    <div>
                        <label for="g_ann" class="block text-sm text-gray-400 mb-1.5">Aniversario</label>
                        <input id="g_ann" type="date" bind:value={form.anniversary}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                    </div>
                    <div class="col-span-2">
                        <label for="g_allergy" class="block text-sm text-gray-400 mb-1.5">Alergias</label>
                        <input id="g_allergy" type="text" bind:value={form.allergies} placeholder="Mariscos, gluten, lactosa..."
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
                    </div>
                    <div class="col-span-2">
                        <label for="g_pref" class="block text-sm text-gray-400 mb-1.5">Preferencias</label>
                        <textarea id="g_pref" bind:value={form.preferences} rows="2"
                            placeholder="Mesa con vista, silla alta para bebé..."
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none resize-none"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label for="g_notes" class="block text-sm text-gray-400 mb-1.5">Notas del staff</label>
                        <textarea id="g_notes" bind:value={form.staff_notes} rows="2"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none resize-none"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" bind:checked={form.is_vip} class="accent-amber-500 w-4 h-4" />
                            <span class="text-sm text-gray-400">Cliente VIP</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors">
                        {processing ? 'Guardando...' : 'Crear cliente'}
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
