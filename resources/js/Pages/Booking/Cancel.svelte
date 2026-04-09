<script>
    import { router } from '@inertiajs/svelte'

    let { reservation, restaurant, canCancel, token } = $props()

    let processing = $state(false)
    let confirmed  = $state(false)

    const submit = () => {
        processing = true
        router.post(`/reservar/cancelar/${token}`, {}, {
            onFinish: () => { processing = false },
        })
    }
</script>

<svelte:head><title>Cancelar reservación — {restaurant.name}</title></svelte:head>

<div class="min-h-screen bg-gray-950 flex flex-col items-center justify-center px-4 py-16">

    <div class="w-full max-w-md">

        <!-- Logo / nombre -->
        <div class="text-center mb-8">
            <p class="text-amber-400 font-bold text-xl tracking-wide">{restaurant.name}</p>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

            <!-- Header -->
            <div class="bg-gray-800/60 px-6 py-5 border-b border-gray-800">
                <h1 class="text-white font-semibold text-lg">Cancelar reservación</h1>
                <p class="text-gray-400 text-sm mt-1">Código: <span class="font-mono text-amber-400">{reservation.confirmation_code}</span></p>
            </div>

            <div class="px-6 py-6 space-y-4">

                <!-- Detalle de la reserva -->
                <div class="bg-gray-950 rounded-xl p-4 space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Titular</span>
                        <span class="text-gray-200 font-medium">{reservation.guest_name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Fecha</span>
                        <span class="text-gray-200">{reservation.reservation_date}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Horario</span>
                        <span class="text-gray-200">{reservation.starts_at} – {reservation.ends_at}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Personas</span>
                        <span class="text-gray-200">{reservation.party_size}</span>
                    </div>
                </div>

                {#if !canCancel}
                    <!-- Estado no cancelable -->
                    <div class="flex items-start gap-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-4">
                        <svg class="w-5 h-5 text-yellow-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-yellow-300 text-sm">
                            Esta reservación no puede cancelarse porque su estado actual es
                            <strong class="font-semibold">{reservation.status}</strong>.
                        </p>
                    </div>

                    <a href="mailto:{restaurant.email}"
                        class="block text-center text-sm text-gray-400 hover:text-amber-400 transition-colors">
                        Contactanos a {restaurant.email}
                    </a>

                {:else if !confirmed}
                    <!-- Confirmación de cancelación -->
                    <div class="flex items-start gap-3 bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                        <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-red-300 text-sm">
                            Esta acción no se puede deshacer. La mesa quedará libre y no recibirás reembolso automático.
                        </p>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <a href="/"
                            class="flex-1 text-center py-2.5 rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800 transition-colors text-sm font-medium">
                            Mantener reserva
                        </a>
                        <button
                            onclick={() => confirmed = true}
                            class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-semibold text-sm transition-colors">
                            Sí, cancelar
                        </button>
                    </div>

                {:else}
                    <!-- Confirmación final -->
                    <p class="text-gray-300 text-sm text-center">
                        ¿Confirmas la cancelación de tu reserva?
                    </p>

                    <button
                        onclick={submit}
                        disabled={processing}
                        class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-500 disabled:opacity-60 text-white font-bold text-sm transition-colors flex items-center justify-center gap-2">
                        {#if processing}
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Cancelando...
                        {:else}
                            Confirmar cancelación
                        {/if}
                    </button>

                    <button
                        onclick={() => confirmed = false}
                        disabled={processing}
                        class="w-full text-center text-sm text-gray-500 hover:text-gray-300 transition-colors">
                        Volver atrás
                    </button>
                {/if}

            </div>
        </div>

        <p class="text-center text-xs text-gray-600 mt-6">
            ¿Necesitás ayuda? Escribinos a
            <a href="mailto:{restaurant.email}" class="text-amber-400 hover:underline">{restaurant.email}</a>
        </p>
    </div>

</div>
