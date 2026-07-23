<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
defineProps<SchemaNodeProps>();
</script>

<template>
    <component
        :is="`h${Math.min(Math.max(node.level || 2, 1), 6)}`"
        class="font-semibold tracking-tight"
    >
        {{ (node.content as string) || node.label }}
    </component>
</template>
