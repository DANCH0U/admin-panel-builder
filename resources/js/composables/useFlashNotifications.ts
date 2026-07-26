import { useNotificationsStore } from '@/stores/useNotificationsStore';
import {
    normalizeNotificationType,
    type SharedNotification,
} from '@/types/notifications';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

let lastSignature = '';

function signature(items: SharedNotification[]): string {
    return items
        .map((n) => `${n.type}:${n.title ?? ''}:${n.message}`)
        .join('|');
}

/**
 * Reads Inertia shared `notifications` and shows toasts (deduped per payload).
 */
export function useFlashNotifications() {
    const page = usePage();
    const store = useNotificationsStore();

    watch(
        () => page.props.notifications as SharedNotification[] | undefined,
        (items) => {
            if (!Array.isArray(items) || items.length === 0) {
                lastSignature = '';
                return;
            }

            const sig = signature(items);
            if (sig === lastSignature) {
                return;
            }
            lastSignature = sig;

            items.forEach((item) => {
                if (!item?.message) return;
                store.addNotification(normalizeNotificationType(item.type), item.message, {
                    title: item.title,
                    duration: item.duration,
                });
            });
        },
        { deep: true, immediate: true },
    );
}
