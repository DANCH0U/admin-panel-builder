import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

/**
 * Panel navigation loading with a configurable delay so fast
 * visits (including DataTable pagination) feel instant.
 */
export function useInertiaLoading() {
    const page = usePage();
    const navigating = ref(false);
    const visible = ref(false);

    const delayMs = computed(() => {
        const panel = page.props.panel as { loading_delay_ms?: number } | undefined;
        const raw = Number(panel?.loading_delay_ms ?? 200);

        return Number.isFinite(raw) && raw >= 0 ? raw : 200;
    });

    let delayTimer: ReturnType<typeof setTimeout> | null = null;

    function clearDelay() {
        if (delayTimer !== null) {
            clearTimeout(delayTimer);
            delayTimer = null;
        }
    }

    function start() {
        navigating.value = true;
        clearDelay();
        delayTimer = setTimeout(() => {
            if (navigating.value) {
                visible.value = true;
            }
        }, delayMs.value);
    }

    function finish() {
        navigating.value = false;
        visible.value = false;
        clearDelay();
    }

    onMounted(() => {
        const offStart = router.on('start', start);
        const offFinish = router.on('finish', finish);
        const offCancel = router.on('cancel', finish);
        const offError = router.on('error', finish);

        onUnmounted(() => {
            offStart();
            offFinish();
            offCancel();
            offError();
            clearDelay();
        });
    });

    return {
        navigating,
        visible,
        delayMs,
    };
}
