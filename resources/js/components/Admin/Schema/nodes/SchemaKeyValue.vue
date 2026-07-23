<script setup lang="ts">
import type { SchemaNodeProps } from '../types';

defineProps<SchemaNodeProps>();
</script>

<template>
    <div class="space-y-3">
        <div v-if="node.label" class="space-y-1">
            <h3 class="text-sm font-semibold tracking-tight">{{ node.label }}</h3>
            <p v-if="node.helpText" class="text-xs text-muted-foreground">{{ node.helpText }}</p>
        </div>

        <dl
            v-if="Array.isArray(node.entries) && node.entries.length"
            class="overflow-hidden rounded-2xl border border-border/70 divide-y divide-border/70 bg-background/40"
        >
            <div
                v-for="(row, index) in node.entries"
                :key="index"
                class="grid gap-1 px-4 py-3 sm:grid-cols-[minmax(8rem,12rem)_1fr] sm:gap-4"
            >
                <dt class="text-sm text-muted-foreground">{{ row.key }}</dt>
                <dd class="min-w-0 text-sm">
                    <pre
                        v-if="row.json"
                        class="overflow-x-auto whitespace-pre-wrap break-words rounded-lg bg-muted/50 p-3 font-mono text-xs leading-relaxed"
                    >{{ row.value }}</pre>
                    <span v-else class="break-words">{{ row.value }}</span>
                </dd>
            </div>
        </dl>

        <p v-else class="text-sm text-muted-foreground">No data.</p>
    </div>
</template>
