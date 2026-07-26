<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { SchemaNodeProps } from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';

const props = defineProps<SchemaNodeProps>();

const columns = computed(() => Math.max(1, Number(props.node.columns || 2)));

const columnsClass = computed(() => {
    const cols = columns.value;
    if (cols <= 1) return 'grid-cols-1';
    if (cols === 2) return 'grid-cols-1 sm:grid-cols-2';
    if (cols === 3) return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
    return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4';
});
</script>

<template>
    <div :class="cn('grid gap-4', columnsClass)">
        <div
            v-for="(child, childIndex) in node.schema || []"
            :key="childIndex"
            :style="
                child.column_span
                    ? {
                          gridColumn: `span ${Math.min(Number(child.column_span), columns)} / span ${Math.min(Number(child.column_span), columns)}`,
                      }
                    : undefined
            "
        >
            <SchemaRenderer :schema="[child]" :form="form" :initial-data="initialData" />
        </div>
    </div>
</template>
