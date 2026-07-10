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

        // El selector de perfiles va sin layout (entrada pre-perfil);
        // el resto se envuelve en el layout compartido.
        if (name !== 'Perfiles/Iniciar') {
            page.default.layout = (h, page) => h(AppLayout, {}, () => page);
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
