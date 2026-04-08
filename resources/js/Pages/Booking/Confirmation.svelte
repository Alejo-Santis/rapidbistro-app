<script>
    import GuestLayout from '../../Layouts/GuestLayout.svelte'

    let { reservation, restaurant } = $props()
</script>

<GuestLayout {restaurant} title="Reservación confirmada">

    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-emerald-500/15 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">¡Reservación confirmada!</h1>
        <p class="text-gray-400 text-sm mt-2">
            Hola <span class="text-white">{reservation.guest_name}</span>, hemos enviado los detalles a <span class="text-amber-400">{reservation.guest_email}</span>.
        </p>
    </div>

    <!-- Código de confirmación -->
    <div class="bg-gray-900 border-2 border-amber-500/30 rounded-2xl p-6 text-center mb-6">
        <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Código de confirmación</p>
        <p class="font-mono text-3xl font-bold text-amber-400 tracking-widest">{reservation.confirmation_code}</p>
        <p class="text-xs text-gray-600 mt-2">Guarda este código para cualquier consulta</p>
    </div>

    <!-- Detalle de la reservación -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-800">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Detalles de tu reservación</p>
        </div>
        <div class="divide-y divide-gray-800">
            <div class="flex justify-between items-center px-5 py-3.5">
                <span class="text-sm text-gray-400">Fecha</span>
                <span class="text-sm text-white font-medium capitalize">{reservation.reservation_date}</span>
            </div>
            <div class="flex justify-between items-center px-5 py-3.5">
                <span class="text-sm text-gray-400">Horario</span>
                <span class="text-sm text-white font-medium">{reservation.starts_at} – {reservation.ends_at}</span>
            </div>
            <div class="flex justify-between items-center px-5 py-3.5">
                <span class="text-sm text-gray-400">Personas</span>
                <span class="text-sm text-white font-medium">{reservation.party_size}</span>
            </div>
            {#if reservation.table_number}
                <div class="flex justify-between items-center px-5 py-3.5">
                    <span class="text-sm text-gray-400">Mesa</span>
                    <span class="text-sm text-white font-medium">
                        {reservation.table_number}
                        {#if reservation.zone_name}<span class="text-gray-500"> — {reservation.zone_name}</span>{/if}
                    </span>
                </div>
            {/if}
            {#if reservation.notes}
                <div class="flex justify-between items-start px-5 py-3.5 gap-4">
                    <span class="text-sm text-gray-400 shrink-0">Notas</span>
                    <span class="text-sm text-gray-300 text-right">{reservation.notes}</span>
                </div>
            {/if}
        </div>
    </div>

    {#if restaurant?.policy}
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl px-4 py-3 mb-6">
            <p class="text-xs text-amber-400/80 leading-relaxed">
                <span class="font-semibold">Política de cancelación:</span> {restaurant.policy}
            </p>
        </div>
    {/if}

    <div class="text-center space-y-3">
        <a
            href="/reservar"
            class="block w-full py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-xl text-sm transition-colors"
        >
            Hacer otra reservación
        </a>
        <p class="text-xs text-gray-600">
            ¿Preguntas? Escríbenos a
            <a href="mailto:{restaurant?.email}" class="text-amber-400/70 hover:text-amber-400">{restaurant?.email}</a>
        </p>
    </div>

</GuestLayout>
