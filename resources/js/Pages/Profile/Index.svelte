<script>
    import AppLayout from '../../Layouts/AppLayout.svelte'
    import PasswordInput from '../../Components/PasswordInput.svelte'
    import { router, page } from '@inertiajs/svelte'
    import { untrack } from 'svelte'

    let { user } = $props()

    // Flash del servidor
    const flash = $derived($page.props.flash ?? {})

    // ── Formulario de perfil ──────────────────────────────────────────────
    let profileForm = $state({
        name:  untrack(() => user.name  ?? ''),
        email: untrack(() => user.email ?? ''),
        phone: untrack(() => user.phone ?? ''),
    })
    let profileErrors     = $state({})
    let profileProcessing = $state(false)

    function submitProfile(e) {
        e.preventDefault()
        profileProcessing = true
        router.put('/profile', { ...profileForm }, {
            onError:  (errs) => { profileErrors = errs },
            onFinish: () => { profileProcessing = false },
        })
    }

    // ── Formulario de contraseña ──────────────────────────────────────────
    let pwForm = $state({
        current_password:      '',
        password:              '',
        password_confirmation: '',
    })
    let pwErrors     = $state({})
    let pwProcessing = $state(false)

    function submitPassword(e) {
        e.preventDefault()
        pwProcessing = true
        router.put('/profile/password', { ...pwForm }, {
            onError:  (errs) => { pwErrors = errs },
            onFinish: () => { pwProcessing = false },
        })
    }

    function getInitials(name) {
        return name
            ?.split(' ')
            .slice(0, 2)
            .map(n => n[0])
            .join('')
            .toUpperCase() ?? '?'
    }
</script>

<AppLayout title="Mi perfil">
    <div class="max-w-3xl space-y-6">

        <!-- Header -->
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-amber-500 flex items-center justify-center text-gray-900 font-bold text-2xl shrink-0">
                {getInitials(user.name)}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{user.name}</h1>
                <p class="text-gray-400 text-sm mt-0.5">{user.email}</p>
            </div>
        </div>

        <!-- Flash success -->
        {#if flash.success}
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-green-300 text-sm">{flash.success}</p>
            </div>
        {/if}

        <!-- ── Información personal ── -->
        <form onsubmit={submitProfile}>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="font-semibold text-white mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Información personal
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div class="sm:col-span-2">
                        <label for="profile-name" class="block text-sm text-gray-400 mb-1.5">Nombre completo *</label>
                        <input id="profile-name" type="text" bind:value={profileForm.name}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {profileErrors.name ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                        {#if profileErrors.name}
                            <p class="mt-1 text-xs text-red-400">{profileErrors.name}</p>
                        {/if}
                    </div>
                    <div>
                        <label for="profile-email" class="block text-sm text-gray-400 mb-1.5">Correo electrónico *</label>
                        <input id="profile-email" type="email" bind:value={profileForm.email}
                            class="w-full px-3 py-2.5 bg-gray-800 border rounded-lg text-white text-sm focus:outline-none
                                {profileErrors.email ? 'border-red-500' : 'border-gray-700 focus:border-amber-500'}" />
                        {#if profileErrors.email}
                            <p class="mt-1 text-xs text-red-400">{profileErrors.email}</p>
                        {/if}
                    </div>
                    <div>
                        <label for="profile-phone" class="block text-sm text-gray-400 mb-1.5">Teléfono</label>
                        <input id="profile-phone" type="tel" bind:value={profileForm.phone}
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" disabled={profileProcessing}
                        class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-60 text-gray-900 font-semibold rounded-lg text-sm transition-colors">
                        {profileProcessing ? 'Guardando...' : 'Guardar cambios'}
                    </button>
                </div>
            </div>
        </form>

        <!-- ── Cambio de contraseña ── -->
        <form onsubmit={submitPassword}>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
                <h2 class="font-semibold text-white mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Cambiar contraseña
                </h2>
                <p class="text-xs text-gray-500 mb-5">Al cambiar tu contraseña se cerrará la sesión automáticamente por seguridad.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div class="sm:col-span-2">
                        <label for="current-password" class="block text-sm text-gray-400 mb-1.5">Contraseña actual *</label>
                        <PasswordInput
                            id="current-password"
                            bind:value={pwForm.current_password}
                            autocomplete="current-password"
                            error={pwErrors.current_password}
                        />
                    </div>
                    <div>
                        <label for="new-password" class="block text-sm text-gray-400 mb-1.5">Nueva contraseña *</label>
                        <PasswordInput
                            id="new-password"
                            bind:value={pwForm.password}
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                            showStrength={true}
                            confirmValue={pwForm.password_confirmation}
                            error={pwErrors.password}
                        />
                    </div>
                    <div>
                        <label for="password-confirmation" class="block text-sm text-gray-400 mb-1.5">Confirmar contraseña *</label>
                        <PasswordInput
                            id="password-confirmation"
                            bind:value={pwForm.password_confirmation}
                            autocomplete="new-password"
                            placeholder="Repetí la contraseña"
                        />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" disabled={pwProcessing}
                        class="px-6 py-2.5 bg-red-600 hover:bg-red-500 disabled:opacity-60 text-white font-semibold rounded-lg text-sm transition-colors">
                        {pwProcessing ? 'Actualizando...' : 'Actualizar contraseña'}
                    </button>
                </div>
            </div>
        </form>

    </div>
</AppLayout>
