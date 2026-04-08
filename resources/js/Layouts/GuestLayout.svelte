<script>
    import { Toaster, toast } from 'svelte-sonner'
    import { page } from '@inertiajs/svelte'
    import { onMount } from 'svelte'

    let { children, restaurant, title = 'Reservaciones' } = $props()

    const flash = $derived($page.props.flash ?? {})

    onMount(() => {
        if (flash.success) toast.success(flash.success)
        if (flash.error)   toast.error(flash.error)
    })
</script>

<svelte:head>
    <title>{title} — {restaurant?.name ?? 'RapidBistro'}</title>
</svelte:head>

<Toaster richColors position="top-right" />

<div class="min-h-screen bg-gray-950">

    <!-- Header público -->
    <header class="border-b border-gray-800 bg-gray-900/80 backdrop-blur sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="font-bold text-white text-sm">{restaurant?.name ?? 'RapidBistro'}</span>
            </div>
            <a href="/login" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Staff →</a>
        </div>
    </header>

    <!-- Contenido -->
    <main class="max-w-2xl mx-auto px-4 py-10">
        {@render children()}
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800 mt-16">
        <div class="max-w-2xl mx-auto px-4 py-6 text-center">
            {#if restaurant?.address}
                <p class="text-xs text-gray-600">{restaurant.address}</p>
            {/if}
            {#if restaurant?.phone || restaurant?.email}
                <p class="text-xs text-gray-600 mt-1">
                    {#if restaurant.phone}{restaurant.phone}{/if}
                    {#if restaurant.phone && restaurant.email} · {/if}
                    {#if restaurant.email}{restaurant.email}{/if}
                </p>
            {/if}
        </div>
    </footer>

</div>
