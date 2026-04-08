<script>
    import GuestLayout from '../../Layouts/GuestLayout.svelte'
    import { page } from '@inertiajs/svelte'

    const restaurant = $derived($page.props.restaurant ?? null)
</script>

<GuestLayout {restaurant} title="En lista de espera">

    <div class="text-center py-8">
        <div class="w-16 h-16 bg-blue-500/15 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">¡Ya estás en la lista!</h1>
        <p class="text-gray-400 text-sm max-w-sm mx-auto leading-relaxed">
            Te notificaremos por correo electrónico en cuanto haya disponibilidad para tu fecha y número de personas.
        </p>

        <div class="mt-8 p-4 bg-gray-900 border border-gray-800 rounded-xl text-left max-w-sm mx-auto">
            <p class="text-xs text-gray-500 leading-relaxed">
                Si consigues disponibilidad en otra fecha o decides no esperar, no es necesario que hagas nada más.
            </p>
        </div>

        <div class="mt-8 space-y-3">
            <a
                href="/reservar"
                class="block w-full max-w-sm mx-auto py-3 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold rounded-xl text-sm transition-colors"
            >
                Intentar otra fecha
            </a>
        </div>
    </div>

</GuestLayout>
