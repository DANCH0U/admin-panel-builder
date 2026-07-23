import {
    type AddNotificationOptions,
    type Notification,
    type NotificationAction,
    type NotificationType,
    normalizeNotificationType,
} from '@/types/notifications';
import { defineStore } from 'pinia';
import { ref } from 'vue';

let nextId = 0;

export const useNotificationsStore = defineStore('notifications', () => {
    const notifications = ref<Notification[]>([]);

    const addNotification = (
        type: string,
        message: string,
        options: AddNotificationOptions = {},
    ) => {
        const normalizedType = normalizeNotificationType(type);
        const duplicate = notifications.value.some(
            (n) => n.type === normalizedType && n.message === message,
        );

        if (duplicate) {
            return;
        }

        const id = ++nextId;
        const hasAction = Boolean(options.action?.label);
        const duration = options.duration ?? (hasAction ? 8000 : 4500);

        notifications.value.push({
            id,
            type: normalizedType,
            message,
            title: options.title,
            action: options.action,
        });

        if (duration > 0) {
            window.setTimeout(() => removeNotification(id), duration);
        }
    };

    const notify = (
        type: NotificationType,
        message: string,
        options?: AddNotificationOptions,
    ) => addNotification(type, message, options);

    const success = (message: string, options?: AddNotificationOptions) =>
        notify('success', message, options);

    const info = (message: string, options?: AddNotificationOptions) =>
        notify('info', message, options);

    const warning = (message: string, options?: AddNotificationOptions) =>
        notify('warning', message, options);

    const danger = (message: string, options?: AddNotificationOptions) =>
        notify('danger', message, options);

    const removeNotification = (id: number) => {
        notifications.value = notifications.value.filter((n) => n.id !== id);
    };

    return {
        notifications,
        addNotification,
        notify,
        success,
        info,
        warning,
        danger,
        removeNotification,
    };
});

export type { NotificationAction, NotificationType };
