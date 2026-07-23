<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { SchemaNodeProps } from '../types';

defineProps<SchemaNodeProps>();
</script>

<template>
    <div :class="cn('rounded-md border border-dashed p-3 text-xs text-muted-foreground')">
        Unsupported schema type: {{ node.type }}
    </div>
</template>
