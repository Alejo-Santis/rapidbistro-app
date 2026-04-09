<script>
    /**
     * PasswordInput — campo de contraseña con:
     *  - toggle show/hide
     *  - barra de fortaleza (solo cuando showStrength=true)
     *  - feedback de coincidencia (solo cuando confirmValue !== undefined)
     *
     * Props:
     *   id              string   — id del input (requerido para label)
     *   value           string   — bind:value del padre
     *   placeholder     string
     *   autocomplete    string
     *   error           string   — mensaje de error del servidor
     *   showStrength    bool     — mostrar barra de fortaleza
     *   confirmValue    string   — valor del campo de confirmación (para mostrar match)
     *   disabled        bool
     */
    let {
        id          = '',
        value       = $bindable(''),
        placeholder = '••••••••',
        autocomplete = 'current-password',
        error        = '',
        showStrength = false,
        confirmValue = undefined,
        disabled     = false,
    } = $props()

    let show = $state(false)

    // ── Fortaleza ────────────────────────────────────────────────────────────
    const strength = $derived.by(() => {
        if (!showStrength || !value) return null
        let score = 0
        if (value.length >= 8)                      score++
        if (value.length >= 12)                     score++
        if (/[A-Z]/.test(value))                    score++
        if (/[0-9]/.test(value))                    score++
        if (/[^A-Za-z0-9]/.test(value))             score++

        if (score <= 1) return { level: 1, label: 'Muy débil',  color: 'bg-red-500' }
        if (score === 2) return { level: 2, label: 'Débil',     color: 'bg-orange-500' }
        if (score === 3) return { level: 3, label: 'Regular',   color: 'bg-yellow-500' }
        if (score === 4) return { level: 4, label: 'Fuerte',    color: 'bg-blue-500' }
        return              { level: 5, label: 'Muy fuerte', color: 'bg-green-500' }
    })

    // ── Coincidencia ─────────────────────────────────────────────────────────
    const match = $derived.by(() => {
        if (confirmValue === undefined || confirmValue === '') return null
        return value === confirmValue
    })
</script>

<div class="relative">
    <input
        {id}
        type={show ? 'text' : 'password'}
        bind:value
        {placeholder}
        {autocomplete}
        {disabled}
        class="w-full px-3 py-2.5 pr-10 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none transition-colors
            {error
                ? 'border-red-500'
                : (match === false)
                    ? 'border-red-500'
                    : (match === true)
                        ? 'border-green-500'
                        : 'border-gray-700 focus:border-amber-500'}"
    />

    <!-- Toggle show/hide -->
    <button
        type="button"
        onclick={() => show = !show}
        tabindex="-1"
        aria-label={show ? 'Ocultar contraseña' : 'Mostrar contraseña'}
        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
    >
        {#if show}
            <!-- ojo tachado -->
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7
                       a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243
                       M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29
                       m7.532 7.532l3.29 3.29M3 3l3.59 3.59
                       m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7
                       a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        {:else}
            <!-- ojo abierto -->
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5
                       c4.478 0 8.268 2.943 9.542 7
                       -1.274 4.057-5.064 7-9.542 7
                       -4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        {/if}
    </button>
</div>

<!-- Error del servidor -->
{#if error}
    <p class="mt-1 text-xs text-red-400">{error}</p>
{/if}

<!-- Barra de fortaleza -->
{#if showStrength && value}
    <div class="mt-2 space-y-1">
        <div class="flex gap-1">
            {#each [1,2,3,4,5] as i}
                <div class="h-1 flex-1 rounded-full transition-colors duration-300
                    {strength && i <= strength.level ? strength.color : 'bg-gray-700'}">
                </div>
            {/each}
        </div>
        <p class="text-xs {strength?.color?.replace('bg-', 'text-') ?? 'text-gray-500'}">
            {strength?.label}
        </p>
    </div>
{/if}

<!-- Coincidencia -->
{#if match !== null}
    <p class="mt-1 text-xs {match ? 'text-green-400' : 'text-red-400'}">
        {match ? '✓ Las contraseñas coinciden' : '✗ Las contraseñas no coinciden'}
    </p>
{/if}
