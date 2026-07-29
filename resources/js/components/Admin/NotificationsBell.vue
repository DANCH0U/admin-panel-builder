<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { useApi } from '@/composables/useApi';
import { cn } from '@/lib/utils';
import { router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type InboxNotification = {
    id: string;
    title?: string | null;
    message: string;
    type?: string;
    url?: string | null;
    read_at?: string | null;
    created_at?: string | null;
    created_at_human?: string | null;
};

const props = defineProps<{
    labels?: Record<string, string>;
}>();

const page = usePage();
const { get, post, del } = useApi();

const open = ref(false);
const loading = ref(false);
const items = ref<InboxNotification[]>([]);
const unreadCount = ref(Number((page.props.auth as any)?.unread_notifications ?? 0));

const labels = computed(() => ({
    notifications: props.labels?.notifications || 'Notifications',
    empty: props.labels?.notifications_empty || 'No notifications yet.',
    markAll: props.labels?.notifications_mark_all || 'Mark all as read',
    markRead: props.labels?.notifications_mark_read || 'Mark as read',
}));

watch(
    () => (page.props.auth as any)?.unread_notifications,
    (value) => {
        unreadCount.value = Number(value ?? 0);
    },
);

watch(open, (isOpen) => {
    if (isOpen) {
        void load();
    }
});

async function load() {
    loading.value = true;
    const { data, error } = await get('/notifications');
    loading.value = false;

    if (error || !data) return;

    items.value = (data.notifications as InboxNotification[]) || [];
    unreadCount.value = Number(data.unread_count ?? unreadCount.value);
}

function typeAccent(type?: string) {
    const map: Record<string, string> = {
        success: 'bg-emerald-500',
        warning: 'bg-amber-500',
        danger: 'bg-red-500',
        info: 'bg-sky-500',
    };
    return map[type || 'info'] ?? map.info;
}

async function markAllRead() {
    const { data, error } = await post('/notifications/read-all');
    if (error) return;

    unreadCount.value = Number(data?.unread_count ?? 0);
    items.value = items.value.map((n) => ({
        ...n,
        read_at: n.read_at || new Date().toISOString(),
    }));
    router.reload({ only: ['auth'], preserveScroll: true, preserveState: true });
}

async function markRead(item: InboxNotification) {
    if (item.read_at) return;

    const { data, error } = await post(`/notifications/${item.id}/read`);
    if (error) return;

    item.read_at = data?.notification?.read_at || new Date().toISOString();
    unreadCount.value = Number(data?.unread_count ?? Math.max(0, unreadCount.value - 1));
}

async function openItem(item: InboxNotification) {
    await markRead(item);
    open.value = false;

    if (item.url) {
        router.visit(item.url);
    }
}

async function removeItem(item: InboxNotification, event: Event) {
    event.stopPropagation();
    const { data, error } = await del(`/notifications/${item.id}`);
    if (error) return;

    items.value = items.value.filter((n) => n.id !== item.id);
    unreadCount.value = Number(data?.unread_count ?? unreadCount.value);
}
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="ghost"
                size="icon-sm"
                class="relative rounded-xl"
                :aria-label="labels.notifications"
                :title="labels.notifications"
            >
                <Bell class="size-4" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute end-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-semibold leading-none text-destructive-foreground"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </Button>
        </PopoverTrigger>

        <PopoverContent align="end" :side-offset="8" class="w-[22rem] p-0">
            <div class="flex items-center justify-between gap-2 border-b border-border px-3 py-2.5">
                <p class="text-sm font-semibold">{{ labels.notifications }}</p>
                <Button
                    v-if="unreadCount > 0"
                    variant="ghost"
                    size="sm"
                    class="h-8 gap-1.5 px-2 text-xs"
                    @click="markAllRead"
                >
                    <CheckCheck class="size-3.5" />
                    {{ labels.markAll }}
                </Button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <div v-if="loading" class="flex items-center justify-center py-10 text-muted-foreground">
                    <Loader2 class="size-5 animate-spin" />
                </div>

                <div
                    v-else-if="!items.length"
                    class="px-4 py-10 text-center text-sm text-muted-foreground"
                >
                    {{ labels.empty }}
                </div>

                <ul v-else class="divide-y divide-border">
                    <li
                        v-for="item in items"
                        :key="item.id"
                        :class="
                            cn(
                                'group relative flex gap-3 px-3 py-3 transition-colors hover:bg-accent/60',
                                !item.read_at && 'bg-accent/30',
                                item.url && 'cursor-pointer',
                            )
                        "
                        @click="openItem(item)"
                    >
                        <span
                            class="mt-1.5 size-2 shrink-0 rounded-full"
                            :class="typeAccent(item.type)"
                        />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p v-if="item.title" class="truncate text-sm font-medium">
                                    {{ item.title }}
                                </p>
                                <Badge
                                    v-if="!item.read_at"
                                    variant="secondary"
                                    class="shrink-0 px-1.5 py-0 text-[10px]"
                                >
                                    new
                                </Badge>
                            </div>
                            <p class="mt-0.5 text-sm leading-snug text-muted-foreground">
                                {{ item.message }}
                            </p>
                            <div class="mt-1.5 flex items-center justify-between gap-2">
                                <p class="text-xs text-muted-foreground/80">
                                    {{ item.created_at_human }}
                                </p>
                                <button
                                    type="button"
                                    class="text-xs text-muted-foreground opacity-0 transition-opacity hover:text-foreground group-hover:opacity-100"
                                    @click="removeItem(item, $event)"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </PopoverContent>
    </Popover>
</template>
