import { useNotificationsStore } from '@/stores/useNotificationsStore';
import type { AddNotificationOptions } from '@/types/notifications';
import { storeToRefs } from 'pinia';

export function useNotifications() {
    const store = useNotificationsStore();
    const { notifications } = storeToRefs(store);

    return {
        notifications,
        addNotification: (
            type: string,
            message: string,
            options?: AddNotificationOptions,
        ) => store.addNotification(type, message, options),
        removeNotification: store.removeNotification,
        success: store.success,
        info: store.info,
        warning: store.warning,
        danger: store.danger,
    };
}
