<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import type { SchemaNodeProps } from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';

defineProps<SchemaNodeProps>();
</script>

<template>
    <section class="space-y-4">
        <div v-if="node.label" class="space-y-1">
            <h3 class="text-base font-semibold tracking-tight">{{ node.label }}</h3>
            <p v-if="node.description || node.helpText" class="text-sm text-muted-foreground">
                {{ node.description || node.helpText }}
            </p>
        </div>
        <Separator />
        <SchemaRenderer
            v-if="node.schema?.length"
            :schema="node.schema"
            :form="form"
            :initial-data="initialData"
        />
    </section>
</template>
