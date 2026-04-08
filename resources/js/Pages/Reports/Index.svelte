<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import { router } from '@inertiajs/svelte'
    import { untrack } from 'svelte'

    let { from, to, summary, byZone, byDayOfWeek, bySlot, topTables } = $props()

    let dateFrom = $state(untrack(() => from))
    let dateTo   = $state(untrack(() => to))

    const applyFilter = () => {
        router.get('/reports', { from: dateFrom, to: dateTo }, { preserveScroll: true })
    }

    const exportPdf = () => {
        window.open(`/reports/export-pdf?date=${dateTo}`, '_blank')
    }

    const exportExcel = () => {
        window.open(`/reports/export-excel?from=${dateFrom}&to=${dateTo}`, '_blank')
    }

    const maxZone    = $derived(Math.max(...byZone.map(z => z.total), 1))
    const maxDay     = $derived(Math.max(...byDayOfWeek.map(d => d.total), 1))
    const maxTable   = $derived(Math.max(...topTables.map(t => t.total), 1))

    const noShowRate = $derived(
        summary.total > 0 ? Math.round((summary.no_show / summary.total) * 100) : 0
    )
    const completionRate = $derived(
        summary.total > 0 ? Math.round((summary.completed / summary.total) * 100) : 0
    )
</script>

<AppLayout title="Reportes">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Reportes</h1>
            <p class="text-gray-400 text-sm mt-0.5">Análisis por período, zona y turno</p>
        </div>
        <div class="flex gap-2">
            <button
                onclick={exportExcel}
                class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-lg text-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </button>
            <button
                onclick={exportPdf}
                class="inline-flex items-center gap-2 px-3 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors border border-gray-700"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                PDF
            </button>
        </div>
    </div>

    <!-- Filtro de fechas -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 mb-6 flex items-end gap-4 flex-wrap">
        <div>
            <label for="rpt_from" class="block text-xs text-gray-400 mb-1.5">Desde</label>
            <input id="rpt_from" type="date" bind:value={dateFrom}
                class="px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
        </div>
        <div>
            <label for="rpt_to" class="block text-xs text-gray-400 mb-1.5">Hasta</label>
            <input id="rpt_to" type="date" bind:value={dateTo}
                class="px-3 py-2 bg-gray-800 border border-gray-700 focus:border-amber-500 rounded-lg text-white text-sm focus:outline-none" />
        </div>
        <button
            onclick={applyFilter}
            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            Aplicar
        </button>
        <p class="text-xs text-gray-600 self-center ml-auto">
            {dateFrom} → {dateTo}
        </p>
    </div>

    <!-- KPIs resumen -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Total reservas</p>
            <p class="text-3xl font-bold text-white">{summary.total}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Comensales</p>
            <p class="text-3xl font-bold text-amber-400">{summary.covers}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Tasa no-show</p>
            <p class="text-3xl font-bold {noShowRate > 10 ? 'text-red-400' : 'text-emerald-400'}">{noShowRate}%</p>
            <p class="text-xs text-gray-600 mt-1">{summary.no_show} reservas</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
            <p class="text-xs text-gray-500 mb-1">Tasa completado</p>
            <p class="text-3xl font-bold text-blue-400">{completionRate}%</p>
            <p class="text-xs text-gray-600 mt-1">{summary.completed} completadas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

        <!-- Por día de la semana -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Reservas por día de la semana</p>
            <div class="space-y-2">
                {#each byDayOfWeek as d}
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-400 w-16 shrink-0">{d.day}</span>
                        <div class="flex-1 h-5 bg-gray-800 rounded overflow-hidden">
                            <div
                                class="h-full bg-amber-500/70 rounded transition-all"
                                style="width:{Math.max((d.total / maxDay) * 100, d.total > 0 ? 4 : 0)}%"
                            ></div>
                        </div>
                        <span class="text-xs font-bold text-gray-300 w-8 text-right shrink-0">{d.total}</span>
                        <span class="text-xs text-gray-600 w-16 shrink-0">{d.covers} pax</span>
                    </div>
                {/each}
            </div>
        </div>

        <!-- Por zona -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Reservas por zona</p>
            {#if byZone.length === 0}
                <p class="text-xs text-gray-600 text-center py-6">Sin datos en este período</p>
            {:else}
                <div class="space-y-2">
                    {#each byZone as z}
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 w-24 shrink-0 truncate">{z.zone}</span>
                            <div class="flex-1 h-5 bg-gray-800 rounded overflow-hidden">
                                <div
                                    class="h-full bg-blue-500/70 rounded transition-all"
                                    style="width:{Math.max((z.total / maxZone) * 100, z.total > 0 ? 4 : 0)}%"
                                ></div>
                            </div>
                            <span class="text-xs font-bold text-gray-300 w-8 text-right shrink-0">{z.total}</span>
                            <span class="text-xs text-gray-600 w-16 shrink-0">{z.covers} pax</span>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

        <!-- Por turno -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Reservas por turno</p>
            {#if bySlot.length === 0}
                <p class="text-xs text-gray-600 text-center py-6">Sin datos en este período</p>
            {:else}
                <div class="space-y-4">
                    {#each bySlot as s}
                        <div class="bg-gray-800/50 rounded-lg p-3">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-semibold text-white">{s.slot}</span>
                                <span class="text-xs text-gray-400">{s.total} reservas · {s.covers} pax</span>
                            </div>
                            <div class="flex gap-4 text-xs text-gray-500">
                                <span>No-shows: <span class="text-red-400 font-semibold">{s.no_shows}</span></span>
                                {#if s.total > 0}
                                    <span>Tasa: <span class="{Math.round(s.no_shows/s.total*100) > 10 ? 'text-red-400' : 'text-gray-400'} font-semibold">{Math.round(s.no_shows/s.total*100)}%</span></span>
                                {/if}
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Top mesas -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <p class="text-sm font-semibold text-white mb-4">Mesas más utilizadas</p>
            {#if topTables.length === 0}
                <p class="text-xs text-gray-600 text-center py-6">Sin datos en este período</p>
            {:else}
                <div class="space-y-2">
                    {#each topTables as t, i}
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600 w-4 shrink-0">{i + 1}</span>
                            <div class="flex-1 h-5 bg-gray-800 rounded overflow-hidden">
                                <div
                                    class="h-full bg-emerald-500/70 rounded transition-all"
                                    style="width:{Math.max((t.total / maxTable) * 100, 4)}%"
                                ></div>
                            </div>
                            <span class="text-xs text-gray-300 shrink-0">Mesa {t.table_number}</span>
                            <span class="text-xs text-gray-500 shrink-0">{t.zone}</span>
                            <span class="text-xs font-bold text-gray-300 w-6 text-right shrink-0">{t.total}</span>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

</AppLayout>
