import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from './Layouts/AppLayout.vue';
import '../css/app.css';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./Paginas/**/*.vue', { eager: true });
        const page = pages[`./Paginas/${name}.vue`];

        if (!page) {
            throw new Error(`Página Inertia no encontrada: ${name}`);
        }

        // Envuelve cada página en el layout compartido.
        page.default.layout = (p) => h(AppLayout, {}, () => p);

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
