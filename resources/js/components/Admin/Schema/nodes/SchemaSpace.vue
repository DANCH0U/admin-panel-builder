<script setup lang="ts">
import { computed } from 'vue';
import type { SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();

const heightClass = computed(() => {
    const size = Number(props.node.size ?? 4);
    const map: Record<number, string> = {
        0: 'h-0',
        1: 'h-1',
        2: 'h-2',
        3: 'h-3',
        4: 'h-4',
        5: 'h-5',
        6: 'h-6',
        8: 'h-8',
        10: 'h-10',
        12: 'h-12',
        16: 'h-16',
        20: 'h-20',
        24: 'h-24',
    };

    return map[size] ?? 'h-4';
});
</script>

<template>
    <div :class="heightClass" aria-hidden="true" />
</template>
