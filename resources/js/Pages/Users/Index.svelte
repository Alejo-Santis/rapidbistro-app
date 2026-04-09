<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import Pagination from '../../Components/Pagination.svelte'
    import { router, page } from '@inertiajs/svelte'

    let { users, roles } = $props()

    let modalOpen = $state(false)
    let editingUser = $state(null)

    const currentUserUuid = $derived($page.props.auth?.user?.uuid)

    let form = $state({
        name: '',
        email: '',
        phone: '',
        password: '',
        role: '',
    })
    let errors = $state({})
    let processing = $state(false)

    const roleColors = {
        'super-admin':  'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'admin':        'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'receptionist': 'bg-green-500/10 text-green-400 border-green-500/20',
        'staff':        'bg-gray-500/10 text-gray-400 border-gray-500/20',
    }

    const roleLabels = {
        'super-admin':  'Super Admin',
        'admin':        'Administrador',
        'receptionist': 'Recepcionista',
        'staff':        'Personal',
    }

    function openCreate() {
        editingUser = null
        form = { name: '', email: '', phone: '', password: '', role: roles[0]?.name ?? '' }
        errors = {}
        modalOpen = true
    }

    function openEdit(user) {
        editingUser = user
        form = {
            name: user.name,
            email: user.email,
            phone: user.phone ?? '',
            password: '',
            role: user.roles[0] ?? '',
        }
        errors = {}
        modalOpen = true
    }

    function closeModal() {
        modalOpen = false
        editingUser = null
        form = { name: '', email: '', phone: '', password: '', role: '' }
        errors = {}
    }

    function submit(e) {
        e.preventDefault()
        processing = true
        if (editingUser) {
            router.put(`/users/${editingUser.uuid}`, { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        } else {
            router.post('/users', { ...form }, {
                onError: (errs) => { errors = errs },
                onSuccess: () => closeModal(),
                onFinish: () => { processing = false },
            })
        }
    }

    function deleteUser(user) {
        if (confirm(`¿Eliminar al usuario "${user.name}"?`)) {
            router.delete(`/users/${user.uuid}`)
        }
    }
</script>

<AppLayout title="Usuarios">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Usuarios</h1>
            <p class="text-gray-400 text-sm mt-1">{users.meta?.total ?? users.data?.length ?? 0} usuarios registrados</p>
        </div>
        <button
            onclick={openCreate}
            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-400 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo usuario
        </button>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        {#if (users.data ?? users).length === 0}
            <div class="text-center py-16 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <p class="text-sm">No hay usuarios</p>
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Usuario</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Contacto</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Rol</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-5 py-3">Registrado</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        {#each (users.data ?? users) as u}
                            <tr class="hover:bg-gray-800/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                                            {u.name?.[0]?.toUpperCase()}
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">
                                                {u.name}
                                                {#if u.uuid === currentUserUuid}
                                                    <span class="ml-1 text-xs text-amber-400">(tú)</span>
                                                {/if}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="text-gray-300">{u.email}</p>
                                    {#if u.phone}
                                        <p class="text-xs text-gray-500">{u.phone}</p>
                                    {/if}
                                </td>
                                <td class="px-5 py-3.5">
                                    {#each (u.roles ?? []) as role}
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {roleColors[role] ?? 'bg-gray-700/50 text-gray-400 border-gray-600'}">
                                            {roleLabels[role] ?? role}
                                        </span>
                                    {/each}
                                </td>
                                <td class="px-5 py-3.5 text-gray-500 text-xs">{u.created_at}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <button
                                            onclick={() => openEdit(u)}
                                            class="text-xs text-amber-400 hover:text-amber-300 transition-colors"
                                        >
                                            Editar
                                        </button>
                                        {#if u.uuid !== currentUserUuid}
                                            <button
                                                onclick={() => deleteUser(u)}
                                                class="text-xs text-red-400 hover:text-red-300 transition-colors"
                                            >
                                                Eliminar
                                            </button>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="px-5">
                <Pagination meta={users.meta} only={['users']} />
            </div>
        {/if}
    </div>
</AppLayout>

<!-- Modal -->
{#if modalOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <button class="absolute inset-0 bg-black/60" onclick={closeModal} aria-label="Cerrar"></button>

        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                <h2 class="font-semibold text-white">{editingUser ? 'Editar usuario' : 'Nuevo usuario'}</h2>
                <button onclick={closeModal} aria-label="Cerrar modal" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form onsubmit={submit} class="p-6 space-y-4">
                <div>
                    <label for="u-name" class="block text-sm text-gray-400 mb-1.5">Nombre *</label>
                    <input
                        id="u-name"
                        type="text"
                        bind:value={form.name}
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                            {errors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                        placeholder="Nombre completo"
                    />
                    {#if errors.name}
                        <p class="mt-1 text-xs text-red-400">{errors.name}</p>
                    {/if}
                </div>

                <div>
                    <label for="u-email" class="block text-sm text-gray-400 mb-1.5">Email *</label>
                    <input
                        id="u-email"
                        type="email"
                        bind:value={form.email}
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                            {errors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                        placeholder="correo@ejemplo.com"
                    />
                    {#if errors.email}
                        <p class="mt-1 text-xs text-red-400">{errors.email}</p>
                    {/if}
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="u-phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                        <input
                            id="u-phone"
                            type="tel"
                            bind:value={form.phone}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                            placeholder="+1 555 0100"
                        />
                    </div>
                    <div>
                        <label for="u-role" class="block text-sm text-gray-400 mb-1.5">Rol *</label>
                        <select
                            id="u-role"
                            bind:value={form.role}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500"
                        >
                            {#each roles as r}
                                <option value={r.name}>{roleLabels[r.name] ?? r.name}</option>
                            {/each}
                        </select>
                        {#if errors.role}
                            <p class="mt-1 text-xs text-red-400">{errors.role}</p>
                        {/if}
                    </div>
                </div>

                <div>
                    <label for="u-password" class="block text-sm text-gray-400 mb-1.5">
                        Contraseña {editingUser ? '(dejar vacío para no cambiar)' : '*'}
                    </label>
                    <input
                        id="u-password"
                        type="password"
                        bind:value={form.password}
                        class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                            {errors.password ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}"
                        placeholder="••••••••"
                    />
                    {#if errors.password}
                        <p class="mt-1 text-xs text-red-400">{errors.password}</p>
                    {/if}
                </div>

                <div class="flex gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:bg-amber-500/50 text-gray-900 font-semibold rounded-lg text-sm transition-colors"
                    >
                        {processing ? 'Guardando...' : (editingUser ? 'Actualizar' : 'Crear usuario')}
                    </button>
                    <button
                        type="button"
                        onclick={closeModal}
                        class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
{/if}
