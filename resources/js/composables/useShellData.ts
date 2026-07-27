import { usePage } from '@inertiajs/vue3';
import { computed, shallowRef, watch } from 'vue';

export type MenuSuffix = {
    value: string;
    type: 'badge' | 'icon' | string;
    color?: string;
};

export type MenuItem = {
    href?: string;
    url?: string;
    title?: string;
    label?: string;
    icon?: string;
    key?: string;
    type?: string;
    disabled?: boolean;
    suffix?: MenuSuffix | null;
};

export function useShellData() {
    const page = usePage();

    const menu = shallowRef<MenuItem[]>(
        ((page.props as any).panel?.menu as MenuItem[]) ?? [],
    );

    watch(
        () => (page.props as any).panel?.menu as MenuItem[] | undefined,
        (value) => {
            menu.value = value ?? [];
        },
    );

    return {
        menu: computed(() => menu.value),
    };
}
