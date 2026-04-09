<script>
    import { Link } from '@inertiajs/svelte'

    let { confirmation_code, restaurant } = $props()
</script>

<svelte:head><title>Reservación cancelada — {restaurant.name}</title></svelte:head>

<div class="min-h-screen bg-gray-950 flex flex-col items-center justify-center px-4 py-16">

    <div class="w-full max-w-md text-center">

        <!-- Ícono -->
        <div class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-2">Reservación cancelada</h1>
        <p class="text-gray-400 text-sm mb-1">
            Tu reservación <span class="font-mono text-amber-400">{confirmation_code}</span> fue cancelada exitosamente.
        </p>
        <p class="text-gray-500 text-sm mb-8">
            Esperamos verte pronto. Podés hacer una nueva reserva cuando quieras.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <Link
                href="/reservar"
                class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold text-sm px-6 py-3 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Hacer una nueva reserva
            </Link>
            <Link
                href="/"
                class="inline-flex items-center justify-center text-sm text-gray-400 hover:text-white transition-colors px-6 py-3">
                Volver al inicio
            </Link>
        </div>

        <p class="mt-10 text-xs text-gray-600">
            {restaurant.name} ·
            <a href="mailto:{restaurant.email}" class="hover:text-amber-400 transition-colors">{restaurant.email}</a>
        </p>

    </div>

</div>
