import { usePage } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAdminStore = defineStore('admin', () => {
    const page = usePage();
    const sideBarKey = ref('dashboard');
    const sideBarOpen = ref(true);
    const loading = ref(false);
    const settings = computed(() => {
        const panel = page.props.panel as Record<string, unknown> | undefined;
        if (!panel) return null;
        return {
            app_name: panel.name,
            logo_url: panel.logo_url,
            navbar_title: panel.navbar_title,
            show_theme_toggle: panel.show_theme_toggle,
        };
    });
    const colorMode = ref('light');

    const setSideBarKey = (key: string) => {
        sideBarKey.value = key;
    };

    return {
        sideBarKey,
        sideBarOpen,
        loading,
        settings,
        colorMode,
        setSideBarKey,
    };
});
