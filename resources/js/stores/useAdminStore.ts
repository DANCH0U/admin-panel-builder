import { usePage } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useAdminStore = defineStore('admin', () => {
    const page = usePage();
    const sideBarKey = ref('dashboard');
    /** Mobile drawer state — desktop keeps the sidebar visible via `lg:` classes. */
    const sideBarOpen = ref(false);
    const loading = ref(false);
    const settings = computed(() => {
        const panel = page.props.panel as Record<string, unknown> | undefined;
        if (!panel) return null;
        return {
            app_name: panel.name,
            logo_url: panel.logo_url,
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
