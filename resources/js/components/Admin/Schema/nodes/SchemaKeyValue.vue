<script setup lang="ts">
import type { SchemaNodeProps } from '../types';

defineProps<SchemaNodeProps>();

function isImageUrl(value: unknown): boolean {
    if (typeof value !== 'string' || !value) return false;
    if (value.startsWith('blob:')) return true;
    if (/\.(png|jpe?g|gif|webp|avif|svg)(\?.*)?$/i.test(value)) return true;
    return value.includes('/storage/');
}
</script>

<template>
    <div class="space-y-3">
        <div v-if="node.label" class="space-y-1">
            <h3 class="text-sm font-semibold leading-snug tracking-tight">{{ node.label }}</h3>
            <p v-if="node.helpText" class="text-xs leading-relaxed text-muted-foreground">
                {{ node.helpText }}
            </p>
        </div>

        <dl
            v-if="Array.isArray(node.entries) && node.entries.length"
            class="overflow-hidden rounded-xl border border-border/70 divide-y divide-border/70 bg-background/40"
        >
            <div
                v-for="(row, index) in node.entries"
                :key="index"
                class="grid gap-2 px-4 py-3 sm:grid-cols-[minmax(8rem,12rem)_1fr] sm:gap-4"
            >
                <dt class="text-sm text-muted-foreground">{{ row.key }}</dt>
                <dd class="min-w-0 text-sm">
                    <pre
                        v-if="row.json"
                        class="overflow-x-auto whitespace-pre-wrap break-words rounded-lg bg-muted/50 p-3 font-mono text-xs leading-relaxed"
                    >{{ row.value }}</pre>
                    <img
                        v-else-if="isImageUrl(row.value)"
                        :src="String(row.value)"
                        alt=""
                        class="max-h-40 rounded-lg border object-cover"
                    />
                    <span v-else class="break-words leading-relaxed">{{ row.value }}</span>
                </dd>
            </div>
        </dl>

        <p v-else class="text-sm text-muted-foreground">No data.</p>
    </div>
</template>
