import '../css/app.css';

import { Icon } from '@iconify/vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        app.use(pinia).use(plugin).use(VueApexCharts).component('Icon', Icon);
        app.mount(el);
    },
    progress: {
        // Panel layout shows AdminLoadingIndicator instead (config: admin.ui.loading_delay_ms).
        delay: 99999,
        showSpinner: false,
        includeCSS: false,
    },
});
