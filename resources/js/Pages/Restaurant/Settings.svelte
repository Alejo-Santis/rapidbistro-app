<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { restaurant } = $props()

    let form = $state({
        name: restaurant.name ?? '',
        address: restaurant.address ?? '',
        phone: restaurant.phone ?? '',
        email: restaurant.email ?? '',
        settings: {
            currency: restaurant.settings?.currency ?? 'USD',
            cancellation_policy: restaurant.settings?.cancellation_policy ?? '',
            default_slot_minutes: restaurant.settings?.default_slot_minutes ?? 90,
        },
    })

    let errors = $state({})
    let processing = $state(false)
    let isDirty = $state(false)

    $effect(() => {
        // Mark dirty whenever form changes (simplified: always dirty after first user interaction)
        isDirty = true
    })

    function submit(e) {
        e.preventDefault()
        processing = true
        router.put('/restaurant/settings', { ...form, settings: { ...form.settings } }, {
            onError: (errs) => { errors = errs },
            onSuccess: () => { isDirty = false },
            onFinish: () => { processing = false },
        })
    }
</script>

<AppLayout title="Configuración del restaurante">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Configuración del restaurante</h1>
        <p class="text-gray-400 text-sm mt-1">Datos generales y políticas de {restaurant.name}</p>
    </div>

    <form onsubmit={submit}>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Información general -->
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Información general</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm text-gray-400 mb-1.5">Nombre del restaurante *</label>
                            <input
                                id="name"
                                type="text"
                                bind:value={form.name}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            />
                            {#if errors.name}
                                <p class="mt-1 text-xs text-red-400">{errors.name}</p>
                            {/if}
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm text-gray-400 mb-1.5">Dirección</label>
                            <input
                                id="address"
                                type="text"
                                bind:value={form.address}
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                                placeholder="Av. Principal 123, Ciudad"
                            />
                        </div>

                        <div>
                            <label for="phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                            <input
                                id="phone"
                                type="tel"
                                bind:value={form.phone}
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                                placeholder="+1 555 0100"
                            />
                        </div>

                        <div>
                            <label for="email" class="block text-sm text-gray-400 mb-1.5">Email de contacto</label>
                            <input
                                id="email"
                                type="email"
                                bind:value={form.email}
                                class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                    {errors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                                placeholder="info@restaurante.com"
                            />
                            {#if errors.email}
                                <p class="mt-1 text-xs text-red-400">{errors.email}</p>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- Configuración operativa -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <h2 class="font-semibold text-white mb-4">Configuración operativa</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="currency" class="block text-sm text-gray-400 mb-1.5">Moneda</label>
                            <select
                                id="currency"
                                bind:value={form.settings.currency}
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                            >
                                <option value="USD">USD - Dólar americano</option>
                                <option value="MXN">MXN - Peso mexicano</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="COP">COP - Peso colombiano</option>
                                <option value="ARS">ARS - Peso argentino</option>
                            </select>
                        </div>

                        <div>
                            <label for="slot_minutes" class="block text-sm text-gray-400 mb-1.5">
                                Duración estándar de reservación (min)
                            </label>
                            <input
                                id="slot_minutes"
                                type="number"
                                bind:value={form.settings.default_slot_minutes}
                                min="15" max="480" step="15"
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                            />
                            {#if errors['settings.default_slot_minutes']}
                                <p class="mt-1 text-xs text-red-400">{errors['settings.default_slot_minutes']}</p>
                            {/if}
                        </div>

                        <div class="sm:col-span-2">
                            <label for="cancellation_policy" class="block text-sm text-gray-400 mb-1.5">
                                Política de cancelación
                            </label>
                            <textarea
                                id="cancellation_policy"
                                bind:value={form.settings.cancellation_policy}
                                rows="3"
                                class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 resize-none"
                                placeholder="Ej. Cancelación gratuita hasta 2 horas antes de la reservación."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-5">
                <!-- Info del restaurante -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-500 flex items-center justify-center text-gray-900 font-bold text-xl">
                            {restaurant.name?.[0]?.toUpperCase()}
                        </div>
                        <div>
                            <p class="font-semibold text-white">{restaurant.name}</p>
                            <p class="text-xs text-gray-500">/{restaurant.slug}</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                        {#if restaurant.address}
                            <p>📍 {restaurant.address}</p>
                        {/if}
                        {#if restaurant.phone}
                            <p>📞 {restaurant.phone}</p>
                        {/if}
                        {#if restaurant.email}
                            <p>✉️ {restaurant.email}</p>
                        {/if}
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <button
                        type="submit"
                        disabled={processing}
                        class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                    >
                        {processing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                </div>

                <!-- Acceso rápido -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Acceso rápido</p>
                    <div class="space-y-2">
                        <a href="/zones" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
                            <span class="text-gray-600">→</span> Gestionar zonas
                        </a>
                        <a href="/tables" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
                            <span class="text-gray-600">→</span> Gestionar mesas
                        </a>
                        <a href="/time-slots" class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
                            <span class="text-gray-600">→</span> Configurar horarios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</AppLayout>
