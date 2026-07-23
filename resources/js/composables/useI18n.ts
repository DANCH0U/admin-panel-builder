import { router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

/**
 * Frontend i18n backed by Inertia-shared translation bags.
 *
 * - `content` is always present
 * - `admin` is present only when auth.user.is_admin is true
 *
 * Helpers:
 * - `t(key)`  → admin bag first (if available), then content
 * - `ta(key)` → admin bag only
 * - `tc(key)` → content bag only
 */
export function useI18n() {
    const page = usePage();

    const serverLocale = computed(() => (page.props.locale as string) || 'en');
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

    const translations = computed(
        () => (page.props.translations as Record<string, Record<string, unknown>>) || {},
    );

    const hasAdminTranslations = computed(() => Boolean(translations.value.admin));

    function lookup(
        bag: Record<string, unknown> | undefined,
        key: string,
        replacements?: Record<string, any>,
    ): string | undefined {
        if (!bag || !key) return undefined;

        const parts = key.split('.');
        let value: unknown = bag;

        for (const part of parts) {
            if (value && typeof value === 'object' && part in (value as object)) {
                value = (value as Record<string, unknown>)[part];
            } else {
                return undefined;
            }
        }

        if (typeof value !== 'string') {
            return undefined;
        }

        let result = value;
        if (replacements) {
            Object.entries(replacements).forEach(([k, v]) => {
                result = result.replace(`:${k}`, String(v));
            });
        }

        return result;
    }

    function t(key: string, replacements?: Record<string, any>): string {
        if (!key) return '';

        if (key.startsWith('admin.')) {
            return lookup(translations.value.admin, key.slice(6), replacements) ?? key;
        }
        if (key.startsWith('content.')) {
            return lookup(translations.value.content, key.slice(8), replacements) ?? key;
        }

        return (
            lookup(translations.value.admin, key, replacements) ??
            lookup(translations.value.content, key, replacements) ??
            key
        );
    }

    function ta(key: string, replacements?: Record<string, any>): string {
        if (!key) return '';
        const normalized = key.startsWith('admin.') ? key.slice(6) : key;
        return lookup(translations.value.admin, normalized, replacements) ?? key;
    }

    function tc(key: string, replacements?: Record<string, any>): string {
        if (!key) return '';
        const normalized = key.startsWith('content.') ? key.slice(8) : key;
        return lookup(translations.value.content, normalized, replacements) ?? key;
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
        t,
        ta,
        tc,
        translations,
        locale,
        hasAdminTranslations,
        changeLocale,
        setLocale,
    };
}
