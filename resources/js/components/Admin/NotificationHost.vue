<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useNotifications } from '@/composables/useNotifications';
import { useFlashNotifications } from '@/composables/useFlashNotifications';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Info,
    TriangleAlert,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, type Component } from 'vue';

useFlashNotifications();

const { notifications, removeNotification } = useNotifications();

const iconMap: Record<string, Component> = {
    success: CheckCircle2,
    info: Info,
    warning: TriangleAlert,
    danger: XCircle,
};

const toneClass: Record<string, string> = {
    success:
        'border-emerald-500/30 bg-emerald-50/95 text-emerald-950 dark:bg-emerald-950/90 dark:text-emerald-50',
    info: 'border-sky-500/30 bg-sky-50/95 text-sky-950 dark:bg-sky-950/90 dark:text-sky-50',
    warning:
        'border-amber-500/30 bg-amber-50/95 text-amber-950 dark:bg-amber-950/90 dark:text-amber-50',
    danger:
        'border-red-500/30 bg-red-50/95 text-red-950 dark:bg-red-950/90 dark:text-red-50',
};

const iconTone: Record<string, string> = {
    success: 'text-emerald-600 dark:text-emerald-400',
    info: 'text-sky-600 dark:text-sky-400',
    warning: 'text-amber-600 dark:text-amber-400',
    danger: 'text-red-600 dark:text-red-400',
};

const visible = computed(() => notifications.value);

function runAction(href?: string | null) {
    if (href) router.visit(href);
}
</script>

<template>
    <div
        class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4"
        aria-live="polite"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="-translate-y-3 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="-translate-y-2 opacity-0 scale-95"
            move-class="transition duration-200"
        >
            <div
                v-for="n in visible"
                :key="n.id"
                :class="
                    cn(
                        'pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-2xl border px-4 py-3 shadow-lg backdrop-blur-md',
                        toneClass[n.type] ?? toneClass.info,
                    )
                "
            >
                <component
                    :is="iconMap[n.type] || Info"
                    :class="cn('mt-0.5 size-5 shrink-0', iconTone[n.type] ?? iconTone.info)"
                />
                <div class="min-w-0 flex-1 space-y-1">
                    <p v-if="n.title" class="text-sm font-semibold leading-tight">{{ n.title }}</p>
                    <p class="text-sm leading-snug opacity-90">{{ n.message }}</p>
                    <Button
                        v-if="n.action?.label"
                        type="button"
                        size="sm"
                        variant="secondary"
                        class="mt-1 h-8 rounded-lg"
                        @click="runAction(n.action.href)"
                    >
                        {{ n.action.label }}
                    </Button>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 opacity-60 transition hover:bg-black/5 hover:opacity-100 dark:hover:bg-white/10"
                    aria-label="Dismiss"
                    @click="removeNotification(n.id)"
                >
                    <X class="size-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
