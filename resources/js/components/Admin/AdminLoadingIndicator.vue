<script setup lang="ts">
import { useInertiaLoading } from '@/composables/useInertiaLoading';
import { cn } from '@/lib/utils';
import { Loader2 } from 'lucide-vue-next';

const { visible } = useInertiaLoading();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="pointer-events-none fixed inset-0 z-[100] flex items-start justify-center"
                aria-live="polite"
                aria-busy="true"
            >
                <!-- Top progress bar -->
                <div
                    :class="
                        cn(
                            'absolute inset-x-0 top-0 h-0.5 overflow-hidden bg-primary/15',
                        )
                    "
                >
                    <div class="admin-loading-bar h-full w-1/3 rounded-full bg-primary" />
                </div>

                <!-- Floating pill (content area feel) -->
                <div
                    class="pointer-events-none mt-4 inline-flex items-center gap-2 rounded-full border border-border/70 bg-card/95 px-3 py-1.5 text-xs font-medium text-foreground shadow-sm backdrop-blur-sm"
                >
                    <Loader2 class="size-3.5 animate-spin text-primary" />
                    <span>Loading…</span>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.admin-loading-bar {
    animation: admin-loading-slide 1s ease-in-out infinite;
}

@keyframes admin-loading-slide {
    0% {
        transform: translateX(-120%);
    }
    100% {
        transform: translateX(420%);
    }
}
</style>
