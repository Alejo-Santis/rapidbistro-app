<script>
    import { router } from '@inertiajs/svelte'

    let { status } = $props()

    let email      = $state('')
    let errors     = $state({})
    let processing = $state(false)

    const submit = (e) => {
        e.preventDefault()
        processing = true
        router.post('/forgot-password', { email }, {
            onError:  (errs) => { errors = errs },
            onFinish: () => { processing = false },
        })
    }
</script>

<svelte:head><title>Recuperar contraseña — RapidBistro</title></svelte:head>

<div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <p class="text-amber-400 font-bold text-xl tracking-wide">RapidBistro</p>
            <h1 class="text-white font-semibold text-lg mt-1">Recuperar contraseña</h1>
            <p class="text-gray-400 text-sm mt-1">
                Ingresá tu email y te enviamos un enlace de recuperación.
            </p>
        </div>

        {#if status}
            <div class="mb-5 flex items-start gap-3 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 text-green-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-green-300 text-sm">{status}</p>
            </div>
        {/if}

        <form onsubmit={submit} class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <div>
                <label for="email" class="block text-sm text-gray-400 mb-1.5">Correo electrónico</label>
                <input
                    id="email"
                    type="email"
                    bind:value={email}
                    autocomplete="email"
                    placeholder="tu@email.com"
                    class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                        {errors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                />
                {#if errors.email}
                    <p class="mt-1 text-xs text-red-400">{errors.email}</p>
                {/if}
            </div>

            <button
                type="submit"
                disabled={processing}
                class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-60 text-gray-900 font-bold text-sm rounded-xl transition-colors"
            >
                {processing ? 'Enviando...' : 'Enviar enlace de recuperación'}
            </button>
        </form>

        <p class="text-center mt-5 text-sm text-gray-500">
            <a href="/login" class="text-amber-400 hover:underline">Volver al inicio de sesión</a>
        </p>
    </div>
</div>
