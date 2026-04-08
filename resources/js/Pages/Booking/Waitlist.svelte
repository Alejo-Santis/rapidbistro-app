<script>
    import GuestLayout from '../../Layouts/GuestLayout.svelte'
    import { router } from '@inertiajs/svelte'

    let { restaurant, prefill } = $props()

    let form = $state({
        guest_name:     '',
        guest_email:    '',
        guest_phone:    '',
        preferred_date: prefill?.date ?? '',
        preferred_time: '',
        party_size:     prefill?.party_size ?? 2,
        notes:          '',
    })

    let errors     = $state({})
    let processing = $state(false)

    const submit = (e) => {
        e.preventDefault()
        processing = true
        errors = {}

        router.post('/reservar/lista-espera', { ...form }, {
            onError:  (errs) => { errors = errs; processing = false },
            onFinish: () => { processing = false },
        })
    }
</script>

<GuestLayout {restaurant} title="Lista de espera">

    <div class="mb-8">
        <a href="/reservar" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">← Volver</a>
        <h1 class="text-2xl font-bold text-white mt-3">Lista de espera</h1>
        <p class="text-gray-400 text-sm mt-1">
            Te notificaremos por email si se libera una mesa para tu fecha y cantidad de personas.
        </p>
    </div>

    <form onsubmit={submit} class="space-y-5">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="wl_date" class="block text-sm text-gray-400 mb-1.5">Fecha preferida *</label>
                    <input
                        id="wl_date"
                        type="date"
                        bind:value={form.preferred_date}
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-xl text-white text-sm focus:outline-none
                            {errors.preferred_date ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                    />
                    {#if errors.preferred_date}
                        <p class="mt-1 text-xs text-red-400">{errors.preferred_date}</p>
                    {/if}
                </div>
                <div>
                    <label for="wl_time" class="block text-sm text-gray-400 mb-1.5">Hora preferida</label>
                    <input
                        id="wl_time"
                        type="time"
                        bind:value={form.preferred_time}
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl text-white text-sm focus:outline-none"
                    />
                </div>
            </div>

            <div>
                <label for="wl_party" class="block text-sm text-gray-400 mb-1.5">
                    Personas: <span class="text-amber-400 font-semibold">{form.party_size}</span>
                </label>
                <input
                    id="wl_party"
                    type="range"
                    bind:value={form.party_size}
                    min="1" max="20"
                    class="w-full accent-amber-500"
                />
            </div>

            <div>
                <label for="wl_name" class="block text-sm text-gray-400 mb-1.5">Nombre completo *</label>
                <input
                    id="wl_name"
                    type="text"
                    bind:value={form.guest_name}
                    placeholder="Tu nombre"
                    class="w-full px-3 py-2.5 bg-gray-800 border rounded-xl text-white text-sm focus:outline-none
                        {errors.guest_name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                />
                {#if errors.guest_name}
                    <p class="mt-1 text-xs text-red-400">{errors.guest_name}</p>
                {/if}
            </div>

            <div>
                <label for="wl_email" class="block text-sm text-gray-400 mb-1.5">Correo electrónico *</label>
                <input
                    id="wl_email"
                    type="email"
                    bind:value={form.guest_email}
                    placeholder="tu@email.com"
                    class="w-full px-3 py-2.5 bg-gray-800 border rounded-xl text-white text-sm focus:outline-none
                        {errors.guest_email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                />
                {#if errors.guest_email}
                    <p class="mt-1 text-xs text-red-400">{errors.guest_email}</p>
                {/if}
            </div>

            <div>
                <label for="wl_phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                <input
                    id="wl_phone"
                    type="tel"
                    bind:value={form.guest_phone}
                    placeholder="+1 555 0000"
                    class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl text-white text-sm focus:outline-none"
                />
            </div>

            <div>
                <label for="wl_notes" class="block text-sm text-gray-400 mb-1.5">Notas adicionales</label>
                <textarea
                    id="wl_notes"
                    bind:value={form.notes}
                    rows="2"
                    placeholder="Cualquier información relevante..."
                    class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-xl text-white text-sm focus:outline-none resize-none"
                ></textarea>
            </div>

        </div>

        <button
            type="submit"
            disabled={processing || !form.guest_name || !form.guest_email}
            class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/40 text-gray-900 font-bold rounded-xl text-sm transition-colors"
        >
            {processing ? 'Registrando...' : 'Unirme a la lista de espera'}
        </button>
    </form>

</GuestLayout>
