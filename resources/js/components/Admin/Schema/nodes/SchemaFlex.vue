<script setup lang="ts">
import { cn } from '@/lib/utils';
import { computed } from 'vue';
import type { SchemaNodeProps } from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';

const props = defineProps<SchemaNodeProps>();

const gapClass = computed(() => {
    const gap = Number(props.node.gap ?? 4);
    const map: Record<number, string> = {
        0: 'gap-0',
        1: 'gap-1',
        2: 'gap-2',
        3: 'gap-3',
        4: 'gap-4',
        5: 'gap-5',
        6: 'gap-6',
        8: 'gap-8',
        10: 'gap-10',
        12: 'gap-12',
    };
    return map[gap] ?? 'gap-4';
});

const justifyClass = computed(() => {
    const map: Record<string, string> = {
        start: 'justify-start',
        end: 'justify-end',
        center: 'justify-center',
        between: 'justify-between',
        around: 'justify-around',
        evenly: 'justify-evenly',
    };
    return map[String(props.node.justify || 'start')] ?? 'justify-start';
});

const alignClass = computed(() => {
    const map: Record<string, string> = {
        start: 'items-start',
        end: 'items-end',
        center: 'items-center',
        stretch: 'items-stretch',
        baseline: 'items-baseline',
    };
    return map[String(props.node.align || 'center')] ?? 'items-center';
});

const sticky = computed(() => Boolean(props.node.sticky));
</script>

<template>
    <!-- Spacer so fixed mobile bar doesn't cover content -->
    <div v-if="sticky" class="h-20 md:hidden" aria-hidden="true" />

    <div
        :class="
            cn(
                'flex',
                gapClass,
                justifyClass,
                alignClass,
                node.direction === 'column' ? 'flex-col' : 'flex-row',
                node.wrap ? 'flex-wrap' : 'flex-nowrap',
                sticky &&
                    'fixed inset-x-0 bottom-0 z-40 border-t border-border/80 bg-background/95 px-4 py-3 shadow-[0_-8px_30px_rgba(0,0,0,0.06)] backdrop-blur-md md:static md:inset-auto md:z-auto md:border-0 md:bg-transparent md:p-0 md:shadow-none md:backdrop-blur-none',
            )
        "
    >
        <SchemaRenderer
            v-if="node.schema?.length"
            class="contents"
            :schema="node.schema"
            :form="form"
            :initial-data="initialData"
        />
    </div>
</template>
