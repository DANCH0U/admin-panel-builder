<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
import { computed } from 'vue';

const props = defineProps<SchemaNodeProps>();

const src = computed(() => {
    const value = props.node.src;
    return typeof value === 'string' && value ? value : null;
});
</script>

<template>
    <div class="space-y-2">
        <div v-if="node.label" class="space-y-1">
            <h3 class="text-sm font-semibold leading-snug tracking-tight">{{ node.label }}</h3>
            <p v-if="node.helpText" class="text-xs leading-relaxed text-muted-foreground">
                {{ node.helpText }}
            </p>
        </div>

        <div
            v-if="src"
            class="overflow-hidden border bg-muted/20"
            :class="node.rounded ? 'rounded-full aspect-square max-w-48' : 'rounded-xl'"
        >
            <img
                :src="src"
                :alt="String(node.label || '')"
                class="h-auto w-full max-h-80 object-cover"
            />
        </div>
        <p v-else class="rounded-xl border border-dashed px-4 py-10 text-center text-sm text-muted-foreground">
            No image
        </p>
    </div>
</template>
