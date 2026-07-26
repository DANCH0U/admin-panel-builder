<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
defineProps<SchemaNodeProps>();
</script>

<template>
    <component
        :is="`h${Math.min(Math.max(node.level || 2, 1), 6)}`"
        class="m-0 font-semibold leading-snug tracking-tight"
        :class="{
            'text-3xl': (node.level || 2) <= 1,
            'text-2xl': (node.level || 2) === 2,
            'text-xl': (node.level || 2) === 3,
            'text-lg': (node.level || 2) >= 4,
        }"
    >
        {{ (node.content as string) || node.label }}
    </component>
</template>
