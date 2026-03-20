<script>
    import { Toaster } from 'svelte-sonner'
    let { children } = $props()
</script>

<svelte:head>
    <title>RapidBistro | Iniciar sesión</title>
</svelte:head>

<Toaster position="top-right" richColors />

<div class="min-h-screen bg-gray-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500 text-gray-900 font-bold text-2xl mb-4">
                R
            </div>
            <h1 class="text-2xl font-bold text-white">RapidBistro</h1>
            <p class="text-gray-400 text-sm mt-1">Sistema de Gestión de Reservaciones</p>
        </div>

        {@render children()}
    </div>
</div>
