import { usePage } from '@inertiajs/vue3';
import { computed, shallowRef, watch } from 'vue';

type MenuItem = {
    href: string;
    label: string;
    icon?: string;
    key?: string;
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
