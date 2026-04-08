<script>
    import { page, Link, router } from '@inertiajs/svelte'
    import { Toaster, toast } from 'svelte-sonner'

    let { children, title = 'RapidBistro' } = $props()

    let sidebarOpen = $state(false)
    let sidebarCollapsed = $state(false)
    let userMenuOpen = $state(false)

    const user = $derived($page.props.auth?.user)

    const navItems = [
        {
            href: '/dashboard',
            label: 'Dashboard',
            icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        },
        {
            href: '/reservations',
            label: 'Reservaciones',
            icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        },
        {
            href: '/floor-map',
            label: 'Mapa de Mesas',
            icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        },
        {
            href: '/timeline',
            label: 'Timeline',
            icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        },
        {
            href: '/maitre',
            label: 'Vista Maître',
            icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        },
        {
            href: '/tables',
            label: 'Mesas',
            icon: 'M4 6h16M4 10h16M4 14h16M4 18h16',
        },
        {
            href: '/waitlist',
            label: 'Lista de Espera',
            icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        {
            href: '/walk-in',
            label: 'Walk-in',
            icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        },
        {
            href: '/guests',
            label: 'Clientes',
            icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        },
        {
            href: '/zones',
            label: 'Zonas',
            icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        },
    ]

    const adminNavItems = [
        {
            href: '/reports',
            label: 'Reportes',
            icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        },
        {
            href: '/special-dates',
            label: 'Eventos',
            icon: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        },
        {
            href: '/time-slots',
            label: 'Horarios',
            icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        {
            href: '/users',
            label: 'Usuarios',
            icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        },
        {
            href: '/restaurant/settings',
            label: 'Configuración',
            icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        },
    ]

    function isActive(href) {
        const url = $page.url
        if (href === '/dashboard') return url === '/dashboard' || url === '/'
        return url.startsWith(href)
    }

    function logout() {
        router.post('/logout')
    }

    function toggleUserMenu() {
        userMenuOpen = !userMenuOpen
    }

    function closeUserMenu() {
        userMenuOpen = false
    }

    function getInitials(name) {
        return name
            ?.split(' ')
            .slice(0, 2)
            .map(n => n[0])
            .join('')
            .toUpperCase() ?? 'U'
    }

    // Flash notifications
    $effect(() => {
        const flash = $page.props.flash
        if (flash?.success) toast.success(flash.success)
        if (flash?.error) toast.error(flash.error)
        if (flash?.warning) toast.warning(flash.warning)
        if (flash?.info) toast.info(flash.info)
    })

    const isAdmin = $derived(
        user?.roles?.includes('super-admin') || user?.roles?.includes('admin')
    )
</script>

<svelte:head>
    <title>{title}</title>
</svelte:head>

<Toaster position="top-right" richColors closeButton />

<div class="flex h-screen bg-gray-950 overflow-hidden">

    <!-- Overlay mobile -->
    {#if sidebarOpen}
        <button
            class="fixed inset-0 z-20 bg-black/50 md:hidden"
            onclick={() => (sidebarOpen = false)}
            aria-label="Cerrar menú"
        ></button>
    {/if}

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col bg-gray-900 border-r border-gray-800 transition-all duration-200 ease-in-out md:relative md:translate-x-0
            {sidebarCollapsed ? 'md:w-16' : 'md:w-64'}
            {sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full w-64'}"
    >
        <!-- Logo + botón colapsar -->
        <div class="flex items-center border-b border-gray-800 flex-shrink-0
            {sidebarCollapsed ? 'justify-center px-0 py-5' : 'gap-3 px-5 py-5'}"
        >
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-amber-500 text-gray-900 font-bold text-lg flex-shrink-0">
                R
            </div>
            {#if !sidebarCollapsed}
                <span class="text-white font-semibold text-lg tracking-tight flex-1 min-w-0">RapidBistro</span>
                <!-- Botón colapsar (solo desktop) -->
                <button
                    onclick={() => (sidebarCollapsed = true)}
                    class="hidden md:flex w-7 h-7 items-center justify-center text-gray-500 hover:text-white hover:bg-gray-800 rounded-lg transition-colors flex-shrink-0"
                    title="Colapsar sidebar"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            {/if}
        </div>

        <!-- Botón expandir (solo desktop, solo cuando colapsado) -->
        {#if sidebarCollapsed}
            <button
                onclick={() => (sidebarCollapsed = false)}
                class="hidden md:flex w-full items-center justify-center py-2 text-gray-500 hover:text-white hover:bg-gray-800 transition-colors border-b border-gray-800"
                title="Expandir sidebar"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
            </button>
        {/if}

        <!-- Nav principal -->
        <nav class="flex-1 overflow-y-auto py-4 space-y-1 {sidebarCollapsed ? 'px-2' : 'px-3'}">
            {#each navItems as item}
                <Link
                    href={item.href}
                    title={sidebarCollapsed ? item.label : ''}
                    class="flex items-center rounded-lg text-sm font-medium transition-colors
                        {sidebarCollapsed ? 'justify-center px-0 py-2.5' : 'gap-3 px-3 py-2.5'}
                        {isActive(item.href)
                            ? 'bg-amber-500/10 text-amber-400'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white'}"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                    </svg>
                    {#if !sidebarCollapsed}
                        {item.label}
                    {/if}
                </Link>
            {/each}

            {#if isAdmin}
                <div class="pt-4 pb-1">
                    {#if !sidebarCollapsed}
                        <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administración</p>
                    {:else}
                        <div class="border-t border-gray-800 mx-1"></div>
                    {/if}
                </div>
                {#each adminNavItems as item}
                    <Link
                        href={item.href}
                        title={sidebarCollapsed ? item.label : ''}
                        class="flex items-center rounded-lg text-sm font-medium transition-colors
                            {sidebarCollapsed ? 'justify-center px-0 py-2.5' : 'gap-3 px-3 py-2.5'}
                            {isActive(item.href)
                                ? 'bg-amber-500/10 text-amber-400'
                                : 'text-gray-400 hover:bg-gray-800 hover:text-white'}"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d={item.icon} />
                        </svg>
                        {#if !sidebarCollapsed}
                            {item.label}
                        {/if}
                    </Link>
                {/each}
            {/if}
        </nav>

        <!-- Usuario con dropdown -->
        <div class="border-t border-gray-800 p-3 relative">
            <!-- Dropdown menu (aparece hacia arriba) -->
            {#if userMenuOpen}
                <!-- Capa para cerrar al hacer click fuera -->
                <button
                    class="fixed inset-0 z-10"
                    onclick={closeUserMenu}
                    aria-label="Cerrar menú de usuario"
                ></button>

                <div class="absolute bottom-full mb-2 z-20 bg-gray-800 border border-gray-700 rounded-xl shadow-xl overflow-hidden
                    {sidebarCollapsed ? 'left-0 right-0 w-52 -left-1' : 'left-3 right-3'}">
                    <!-- Info del usuario en el dropdown -->
                    <div class="px-4 py-3 border-b border-gray-700">
                        <p class="text-sm font-medium text-white truncate">{user?.name ?? ''}</p>
                        <p class="text-xs text-gray-400 truncate">{user?.email ?? ''}</p>
                    </div>
                    <!-- Opciones -->
                    <div class="p-1.5 space-y-0.5">
                        <a
                            href="/profile"
                            onclick={closeUserMenu}
                            class="flex items-center gap-3 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Mi perfil
                        </a>
                        <button
                            onclick={logout}
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar sesión
                        </button>
                    </div>
                </div>
            {/if}

            <!-- Botón del usuario (trigger del dropdown) -->
            <button
                onclick={toggleUserMenu}
                title={sidebarCollapsed ? (user?.name ?? '') : ''}
                class="w-full flex items-center rounded-xl transition-colors
                    {sidebarCollapsed ? 'justify-center px-0 py-2' : 'gap-3 px-3 py-2.5'}
                    {userMenuOpen ? 'bg-gray-800' : 'hover:bg-gray-800'}"
            >
                <!-- Avatar con iniciales -->
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-gray-900 font-semibold text-sm flex-shrink-0">
                    {getInitials(user?.name)}
                </div>
                {#if !sidebarCollapsed}
                    <!-- Nombre y email -->
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-sm font-medium text-white truncate">{user?.name ?? ''}</p>
                        <p class="text-xs text-gray-400 truncate">{user?.email ?? ''}</p>
                    </div>
                    <!-- Chevron -->
                    <svg
                        class="w-4 h-4 text-gray-500 flex-shrink-0 transition-transform duration-200 {userMenuOpen ? 'rotate-180' : ''}"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                    </svg>
                {/if}
            </button>
        </div>
    </aside>

    <!-- Contenido principal -->
    <div class="flex-1 flex flex-col min-w-0 overflow-auto">
        <!-- Top bar (mobile) -->
        <header class="flex items-center gap-4 px-4 py-3 bg-gray-900 border-b border-gray-800 md:hidden">
            <!-- Botón hamburguesa -->
            <button
                onclick={() => (sidebarOpen = !sidebarOpen)}
                class="w-9 h-9 flex items-center justify-center text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors flex-shrink-0"
                aria-label="Menú"
            >
                {#if sidebarOpen}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                {:else}
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                {/if}
            </button>

            <!-- Logo + título -->
            <div class="flex items-center gap-2 flex-1">
                <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center text-gray-900 font-bold text-sm">R</div>
                <span class="text-white font-semibold text-sm">{title}</span>
            </div>

            <!-- Avatar usuario mobile -->
            <a href="/profile" class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-gray-900 font-semibold text-sm flex-shrink-0">
                {getInitials(user?.name)}
            </a>
        </header>

        <!-- Page content -->
        <main class="flex-1 p-6">
            {@render children()}
        </main>
    </div>
</div>
