<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
defineProps<SchemaNodeProps>();
</script>

<template>
    <p
        class="m-0 text-sm leading-relaxed"
        :class="{
            'text-muted-foreground': node.variant === 'subdued' || node.variant === 'caption',
            'text-xs': node.variant === 'caption',
        }"
    >
        {{ (node.content as string) || node.label }}
    </p>
</template>
