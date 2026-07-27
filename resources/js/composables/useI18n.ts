import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

/**
 * Locale helpers for document lang/dir + cookie.
 * UI copy is translated on the backend with __('…') — not shared as a JS bag.
 */
export function useI18n() {
    const page = usePage();

    const serverLocale = computed(() => {
        const panel = page.props.panel as { locale?: string } | undefined;
        return panel?.locale || 'en';
    });

    const locale = ref(serverLocale.value);

    watch(
        serverLocale,
        (newVal) => {
            locale.value = newVal;
            setLocale();
        },
        { immediate: true },
    );

    function setCookie(name: string, value: string, days: number = 365) {
        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie =
            name + '=' + (value || '') + '; expires=' + date.toUTCString() + '; path=/; SameSite=Lax';
    }

    function setLocale() {
        if (!locale.value) return;

        const htmlEl = document.documentElement;
        htmlEl.lang = locale.value;
        htmlEl.dir = locale.value === 'ar' ? 'rtl' : 'ltr';
        setCookie('locale', locale.value);
    }

    function changeLocale(newLocale: string) {
        const returnTo =
            window.location.pathname + window.location.search + window.location.hash;
        router.get(`/locale/${newLocale}`, { return: returnTo });
    }

    return {
        locale,
        changeLocale,
        setLocale,
    };
}
