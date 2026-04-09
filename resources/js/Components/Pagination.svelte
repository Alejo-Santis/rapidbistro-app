<script>
    import { router } from '@inertiajs/svelte'

    /**
     * meta  — objeto meta de Laravel paginator (from, to, total, last_page, links[])
     * only  — array de keys de props a preservar al navegar (opcional)
     */
    let { meta, only = [] } = $props()

    function visit(url) {
        if (!url) return
        router.visit(url, {
            preserveState: true,
            preserveScroll: false,
            only: only.length ? only : undefined,
        })
    }

    // Extrae solo los links de páginas (excluye « Anterior » y « Siguiente »)
    const pageLinks = $derived(meta?.links?.slice(1, -1) ?? [])
    const prevLink  = $derived(meta?.links?.[0] ?? null)
    const nextLink  = $derived(meta?.links?.[meta.links.length - 1] ?? null)
</script>

{#if meta && meta.total > 0}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-1 py-4 border-t border-gray-800">

        <!-- Contador -->
        <p class="text-xs text-gray-500 shrink-0">
            {#if meta.last_page > 1}
                Mostrando <span class="text-gray-300">{meta.from}–{meta.to}</span> de <span class="text-gray-300">{meta.total}</span> registros
            {:else}
                <span class="text-gray-300">{meta.total}</span> {meta.total === 1 ? 'registro' : 'registros'}
            {/if}
        </p>

        <!-- Botones (solo si hay más de 1 página) -->
        {#if meta.last_page > 1}
            <div class="flex items-center gap-1">

                <!-- Anterior -->
                <button
                    onclick={() => visit(prevLink?.url)}
                    disabled={!prevLink?.url}
                    class="px-2.5 py-1.5 text-xs rounded-lg transition-colors
                        {prevLink?.url
                            ? 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white'
                            : 'bg-gray-800/40 text-gray-600 cursor-not-allowed'}"
                    aria-label="Página anterior"
                >
                    ←
                </button>

                <!-- Páginas numeradas (máx. 7 visibles con elipsis) -->
                {#each pageLinks as link}
                    {#if link.label === '...'}
                        <span class="px-2 py-1.5 text-xs text-gray-600">…</span>
                    {:else}
                        <button
                            onclick={() => visit(link.url)}
                            disabled={link.active}
                            class="min-w-[30px] px-2.5 py-1.5 text-xs rounded-lg transition-colors
                                {link.active
                                    ? 'bg-amber-500 text-gray-900 font-semibold cursor-default'
                                    : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white'}"
                        >
                            {link.label}
                        </button>
                    {/if}
                {/each}

                <!-- Siguiente -->
                <button
                    onclick={() => visit(nextLink?.url)}
                    disabled={!nextLink?.url}
                    class="px-2.5 py-1.5 text-xs rounded-lg transition-colors
                        {nextLink?.url
                            ? 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white'
                            : 'bg-gray-800/40 text-gray-600 cursor-not-allowed'}"
                    aria-label="Página siguiente"
                >
                    →
                </button>

            </div>
        {/if}
    </div>
{/if}
