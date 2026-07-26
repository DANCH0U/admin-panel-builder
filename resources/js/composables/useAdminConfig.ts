import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type PanelShared = {
    key?: string;
    name?: string;
    prefix?: string;
    path?: string;
    logo_url?: string | null;
    navbar_title?: string | null;
    show_theme_toggle?: boolean;
    locale?: string;
    language?: {
        label: string;
        locale: string;
        font: string;
        family?: string;
    } | null;
    languages?: Array<{
        label: string;
        locale: string;
        font: string;
        family?: string;
    }>;
    menu?: unknown[];
    loading_delay_ms?: number;
};

export function useAdminConfig() {
    const page = usePage();

    const panel = computed(
        () => (page.props.panel as PanelShared | undefined) ?? {},
    );

    const prefix = computed(() => panel.value.prefix || 'admin');
    const basePath = computed(() => panel.value.path || `/${prefix.value}`);

    function adminPath(path: string = ''): string {
        const cleaned = path.replace(/^\/+/, '');
        return cleaned ? `${basePath.value}/${cleaned}` : basePath.value;
    }

    return {
        panel,
        /** @deprecated use `panel` — kept for older call sites */
        admin: panel,
        settings: computed(() => ({
            app_name: panel.value.name,
            logo_url: panel.value.logo_url,
            navbar_title: panel.value.navbar_title,
            show_theme_toggle: panel.value.show_theme_toggle,
        })),
        prefix,
        basePath,
        adminPath,
        name: computed(() => panel.value.name || 'Admin Panel'),
        logoUrl: computed(() => panel.value.logo_url ?? null),
        navbarTitle: computed(() => panel.value.navbar_title ?? null),
        showThemeToggle: computed(() => panel.value.show_theme_toggle ?? true),
        loadingDelayMs: computed(() => {
            const raw = Number(panel.value.loading_delay_ms ?? 200);
            return Number.isFinite(raw) && raw >= 0 ? raw : 200;
        }),
        loginPath: computed(() => adminPath('login')),
    };
}
