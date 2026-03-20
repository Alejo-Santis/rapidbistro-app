import './bootstrap';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/svelte'
import { mount } from 'svelte'

createInertiaApp({
    title: (title) => title ? `RapidBistro | ${title}` : 'RapidBistro',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.svelte', { eager: true })
        return pages[`./Pages/${name}.svelte`]
    },
    setup({ el, App, props }) {
        mount(App, { target: el, props })
    },
    progress: {
        color: '#f59e0b',
        includeCSS: true,
        showSpinner: false,
    },
})
