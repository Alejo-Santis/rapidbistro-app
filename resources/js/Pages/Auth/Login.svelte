<script>
    import AuthLayout from '../../Layouts/AuthLayout.svelte'
    import { router } from '@inertiajs/svelte'

    import { page } from '@inertiajs/svelte'

    let email = $state('')
    let password = $state('')
    let remember = $state(false)
    let errors = $state({})
    let processing = $state(false)

    const status = $derived($page.props.flash?.status ?? null)

    function submit(e) {
        e.preventDefault()
        processing = true
        router.post('/login', { email, password, remember }, {
            onError: (errs) => { errors = errs },
            onFinish: () => { processing = false },
        })
    }
</script>

<AuthLayout>
    {#if status}
        <div class="mb-4 flex items-start gap-3 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 text-green-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-green-300 text-sm">{status}</p>
        </div>
    {/if}

    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8 shadow-xl">
        <h2 class="text-xl font-semibold text-white mb-6">Iniciar sesión</h2>

        <form onsubmit={submit} class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Correo electrónico
                </label>
                <input
                    id="email"
                    type="email"
                    bind:value={email}
                    autocomplete="email"
                    class="w-full px-3.5 py-2.5 bg-gray-800 border rounded-lg text-white placeholder-gray-500 text-sm transition-colors
                        {errors.email ? 'border-red-500 focus:border-red-500' : 'border-gray-700 focus:border-amber-500'}
                        focus:outline-none focus:ring-0"
                    placeholder="admin@rapidbistro.com"
                />
                {#if errors.email}
                    <p class="mt-1.5 text-xs text-red-400">{errors.email}</p>
                {/if}
            </div>

            <!-- Contraseña -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">
                    Contraseña
                </label>
                <input
                    id="password"
                    type="password"
                    bind:value={password}
                    autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 bg-gray-800 border rounded-lg text-white placeholder-gray-500 text-sm transition-colors
                        {errors.password ? 'border-red-500 focus:border-red-500' : 'border-gray-700 focus:border-amber-500'}
                        focus:outline-none focus:ring-0"
                    placeholder="••••••••"
                />
                {#if errors.password}
                    <p class="mt-1.5 text-xs text-red-400">{errors.password}</p>
                {/if}
            </div>

            <!-- Recordarme + Olvidé contraseña -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <input
                        id="remember"
                        type="checkbox"
                        bind:checked={remember}
                        class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-amber-500 focus:ring-amber-500 focus:ring-offset-gray-900"
                    />
                    <label for="remember" class="text-sm text-gray-400">Recordarme</label>
                </div>
                <a href="/forgot-password" class="text-xs text-amber-400 hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                disabled={processing}
                class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-900"
            >
                {#if processing}
                    Ingresando...
                {:else}
                    Ingresar
                {/if}
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-600 mt-6">
        RapidBistro &copy; {new Date().getFullYear()}
    </p>
</AuthLayout>
