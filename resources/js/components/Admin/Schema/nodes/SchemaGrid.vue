<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';

defineProps<SchemaNodeProps>();
</script>

<template>
    <div
        class="grid gap-4"
        :style="{ gridTemplateColumns: `repeat(${node.columns || 2}, minmax(0, 1fr))` }"
    >
        <div
            v-for="(child, childIndex) in node.schema || []"
            :key="childIndex"
            :style="
                child.column_span
                    ? { gridColumn: `span ${child.column_span} / span ${child.column_span}` }
                    : undefined
            "
        >
            <SchemaRenderer :schema="[child]" :form="form" :initial-data="initialData" />
        </div>
    </div>
</template>
