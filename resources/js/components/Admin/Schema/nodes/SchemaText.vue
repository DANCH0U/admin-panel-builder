<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
defineProps<SchemaNodeProps>();
</script>

<template>
    <p
        class="text-sm"
        :class="{
            'text-muted-foreground': node.variant === 'subdued' || node.variant === 'caption',
        }"
    >
        {{ (node.content as string) || node.label }}
    </p>
</template>
