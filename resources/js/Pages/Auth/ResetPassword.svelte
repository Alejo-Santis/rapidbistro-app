<script>
    import { router } from '@inertiajs/svelte'

    let { token, email: initialEmail } = $props()

    let form = $state({
        token,
        email:                 initialEmail ?? '',
        password:              '',
        password_confirmation: '',
    })
    let errors     = $state({})
    let processing = $state(false)

    const submit = (e) => {
        e.preventDefault()
        processing = true
        router.post('/reset-password', { ...form }, {
            onError:  (errs) => { errors = errs },
            onFinish: () => { processing = false },
        })
    }
</script>

<svelte:head><title>Nueva contraseña — RapidBistro</title></svelte:head>

<div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <p class="text-amber-400 font-bold text-xl tracking-wide">RapidBistro</p>
            <h1 class="text-white font-semibold text-lg mt-1">Crear nueva contraseña</h1>
        </div>

        <form onsubmit={submit} class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">

            <div>
                <label for="email" class="block text-sm text-gray-400 mb-1.5">Correo electrónico</label>
                <input
                    id="email"
                    type="email"
                    bind:value={form.email}
                    autocomplete="email"
                    class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                        {errors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                />
                {#if errors.email}
                    <p class="mt-1 text-xs text-red-400">{errors.email}</p>
                {/if}
            </div>

            <div>
                <label for="password" class="block text-sm text-gray-400 mb-1.5">Nueva contraseña</label>
                <input
                    id="password"
                    type="password"
                    bind:value={form.password}
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                    class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                        {errors.password ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                />
                {#if errors.password}
                    <p class="mt-1 text-xs text-red-400">{errors.password}</p>
                {/if}
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm text-gray-400 mb-1.5">Confirmar contraseña</label>
                <input
                    id="password_confirmation"
                    type="password"
                    bind:value={form.password_confirmation}
                    autocomplete="new-password"
                    placeholder="Repetí la contraseña"
                    class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                />
            </div>

            <button
                type="submit"
                disabled={processing}
                class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-60 text-gray-900 font-bold text-sm rounded-xl transition-colors"
            >
                {processing ? 'Guardando...' : 'Restablecer contraseña'}
            </button>
        </form>
    </div>
</div>
