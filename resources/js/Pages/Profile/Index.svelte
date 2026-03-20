<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { user } = $props()

    let form = $state({
        name: user.name ?? '',
        email: user.email ?? '',
        phone: user.phone ?? '',
        password: '',
        password_confirmation: '',
    })

    let errors = $state({})
    let processing = $state(false)

    function submit(e) {
        e.preventDefault()
        processing = true
        router.put('/profile', { ...form }, {
            onError: (errs) => { errors = errs },
            onFinish: () => { processing = false },
        })
    }

    function getInitials(name) {
        return name
            ?.split(' ')
            .slice(0, 2)
            .map(n => n[0])
            .join('')
            .toUpperCase() ?? '?'
    }
</script>

<AppLayout title="Mi perfil">
    <div class="max-w-3xl">
        <!-- Header -->
        <div class="mb-8 flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-amber-500 flex items-center justify-center text-gray-900 font-bold text-2xl flex-shrink-0">
                {getInitials(user.name)}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{user.name}</h1>
                <p class="text-gray-400 text-sm mt-0.5">{user.email}</p>
            </div>
        </div>

        <form onsubmit={submit} class="space-y-5">

            <!-- Información personal -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="font-semibold text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Información personal
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm text-gray-400 mb-1.5">Nombre completo *</label>
                        <input
                            type="text"
                            bind:value={form.name}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            placeholder="Tu nombre completo"
                        />
                        {#if errors.name}
                            <p class="mt-1 text-xs text-red-400">{errors.name}</p>
                        {/if}
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Correo electrónico *</label>
                        <input
                            type="email"
                            bind:value={form.email}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {errors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            placeholder="correo@ejemplo.com"
                        />
                        {#if errors.email}
                            <p class="mt-1 text-xs text-red-400">{errors.email}</p>
                        {/if}
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                        <input
                            type="tel"
                            bind:value={form.phone}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                            placeholder="+1 555 0100"
                        />
                    </div>
                </div>
            </div>

            <!-- Cambiar contraseña -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="font-semibold text-white mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Cambiar contraseña
                </h2>
                <p class="text-xs text-gray-500 mb-5">Déjalo en blanco si no deseas cambiarla</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Nueva contraseña</label>
                        <input
                            type="password"
                            bind:value={form.password}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {errors.password ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                        {#if errors.password}
                            <p class="mt-1 text-xs text-red-400">{errors.password}</p>
                        {/if}
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Confirmar contraseña</label>
                        <input
                            type="password"
                            bind:value={form.password_confirmation}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex items-center justify-between">
                <a
                    href="/dashboard"
                    class="px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors"
                >
                    Volver
                </a>
                <button
                    type="submit"
                    disabled={processing}
                    class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                >
                    {processing ? 'Guardando...' : 'Guardar cambios'}
                </button>
            </div>
        </form>
    </div>
</AppLayout>
