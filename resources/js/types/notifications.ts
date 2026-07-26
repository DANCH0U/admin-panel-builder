export type NotificationType = 'success' | 'info' | 'warning' | 'danger';

export interface SharedNotification {
    type: string;
    message: string;
    title?: string;
    duration?: number;
}

export interface Notification {
    id: number;
    type: NotificationType;
    message: string;
    title?: string;
}

export interface AddNotificationOptions {
    duration?: number;
    title?: string;
}

export function normalizeNotificationType(type: string): NotificationType {
    switch (type) {
        case 'success':
            return 'success';
        case 'info':
        case 'message':
            return 'info';
        case 'warning':
        case 'warn':
            return 'warning';
        case 'danger':
        case 'error':
            return 'danger';
        default:
            return 'info';
    }
}

export function parseFlashPayload(
    payload: unknown,
): { message: string; title?: string; duration?: number } | null {
    if (!payload) {
        return null;
    }

    if (typeof payload === 'string') {
        return { message: payload };
    }

    if (typeof payload === 'object' && payload !== null && 'message' in payload) {
        const data = payload as SharedNotification;

        if (typeof data.message !== 'string' || data.message === '') {
            return null;
        }

        return {
            message: data.message,
            title: data.title,
            duration: data.duration,
        };
    }

    return null;
}
