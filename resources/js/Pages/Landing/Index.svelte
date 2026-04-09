<script>
    import { Link } from '@inertiajs/svelte'

    let { restaurant, schedule } = $props()

    const name    = restaurant?.name    ?? 'RapidBistro'
    const address = restaurant?.address ?? ''
    const phone   = restaurant?.phone   ?? ''
    const email   = restaurant?.email   ?? ''
    const policy  = restaurant?.policy  ?? null

    const dayAbbr = {
        'Lunes': 'Lu', 'Martes': 'Ma', 'Miércoles': 'Mi',
        'Jueves': 'Ju', 'Viernes': 'Vi', 'Sábado': 'Sá', 'Domingo': 'Do',
    }
</script>

<svelte:head><title>{name}</title></svelte:head>

<div class="min-h-screen bg-gray-950 text-white flex flex-col">

    <!-- ── Navbar ── -->
    <header class="fixed top-0 inset-x-0 z-30 bg-gray-950/80 backdrop-blur border-b border-gray-800/60">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <span class="text-amber-400 font-bold tracking-wide text-lg">{name}</span>
            <a href="/login" class="text-sm text-gray-400 hover:text-white transition-colors">
                Acceso personal
            </a>
        </div>
    </header>

    <!-- ── Hero ── -->
    <section class="relative flex flex-col items-center justify-center text-center px-4 pt-32 pb-24 flex-1">
        <!-- Fondo degradado decorativo -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-[600px] h-[400px] rounded-full bg-amber-500/10 blur-3xl"></div>
        </div>

        <div class="relative max-w-2xl mx-auto">
            <p class="text-amber-400 text-sm font-semibold tracking-widest uppercase mb-3">Bienvenido a</p>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight mb-4">{name}</h1>

            {#if address}
                <p class="text-gray-400 text-base mb-10">{address}</p>
            {/if}

            <!-- CTAs principales -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    href="/reservar"
                    class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-bold text-base px-8 py-4 rounded-xl transition-colors shadow-lg shadow-amber-500/20"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Reservar una mesa
                </Link>

                <Link
                    href="/reservar/lista-espera"
                    class="inline-flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-700 text-white font-semibold text-base px-8 py-4 rounded-xl transition-colors border border-gray-700"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Unirme a lista de espera
                </Link>
            </div>
        </div>
    </section>

    <!-- ── Info + Horarios ── -->
    <section class="max-w-4xl mx-auto w-full px-4 pb-20 grid sm:grid-cols-2 gap-6">

        <!-- Contacto -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-xs font-semibold text-amber-400 uppercase tracking-widest mb-4">Información</h2>
            <ul class="space-y-3">
                {#if address}
                    <li class="flex items-start gap-3 text-sm text-gray-300">
                        <svg class="w-4 h-4 mt-0.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {address}
                    </li>
                {/if}
                {#if phone}
                    <li class="flex items-center gap-3 text-sm">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <a href="tel:{phone}" class="text-gray-300 hover:text-amber-400 transition-colors">{phone}</a>
                    </li>
                {/if}
                {#if email}
                    <li class="flex items-center gap-3 text-sm">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <a href="mailto:{email}" class="text-gray-300 hover:text-amber-400 transition-colors">{email}</a>
                    </li>
                {/if}
            </ul>

            {#if policy}
                <div class="mt-5 pt-4 border-t border-gray-800">
                    <p class="text-xs text-gray-500 leading-relaxed">
                        <span class="text-amber-400 font-medium">Cancelaciones:</span> {policy}
                    </p>
                </div>
            {/if}
        </div>

        <!-- Horarios -->
        {#if schedule.length > 0}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h2 class="text-xs font-semibold text-amber-400 uppercase tracking-widest mb-4">Horarios</h2>
                <ul class="space-y-2.5">
                    {#each schedule as day}
                        <li class="flex items-start justify-between text-sm gap-2">
                            <span class="text-gray-400 w-20 shrink-0">{day.day}</span>
                            <span class="text-gray-300 text-right">
                                {#each day.slots as slot, i}
                                    {#if i > 0}<span class="text-gray-600"> · </span>{/if}
                                    <span class="text-xs font-medium">{slot.name}</span>
                                    <span class="text-gray-500"> {slot.opens_at}–{slot.closes_at}</span>
                                {/each}
                            </span>
                        </li>
                    {/each}
                </ul>
            </div>
        {:else}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex flex-col items-center justify-center text-center gap-3">
                <svg class="w-8 h-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 text-sm">Consultá horarios por teléfono o email.</p>
            </div>
        {/if}
    </section>

    <!-- ── Footer ── -->
    <footer class="border-t border-gray-800 py-6 text-center">
        <p class="text-xs text-gray-600">© {new Date().getFullYear()} {name}. Todos los derechos reservados.</p>
    </footer>

</div>
