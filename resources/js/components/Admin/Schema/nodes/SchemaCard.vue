<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { SchemaNodeProps } from '../types';
import { cn } from '@/lib/utils';
import SchemaRenderer from '../SchemaRenderer.vue';

defineProps<SchemaNodeProps>();
</script>

<template>
    <component
        :is="node.bordered ? Card : 'div'"
        :class="
            cn(
                node.bordered
                    ? 'rounded-2xl border-border/70 bg-background/50 shadow-none'
                    : 'space-y-4',
            )
        "
    >
        <CardHeader v-if="node.bordered && (node.label || node.helpText)" class="pb-3">
            <CardTitle v-if="node.label" class="text-base">{{ node.label }}</CardTitle>
            <CardDescription v-if="node.helpText">{{ node.helpText }}</CardDescription>
        </CardHeader>
        <div v-else-if="node.label || node.helpText" class="space-y-1">
            <h3 v-if="node.label" class="text-base font-semibold tracking-tight">{{ node.label }}</h3>
            <p v-if="node.helpText" class="text-sm text-muted-foreground">{{ node.helpText }}</p>
        </div>

        <component :is="node.bordered ? CardContent : 'div'" :class="node.bordered ? undefined : 'space-y-4'">
            <SchemaRenderer
                v-if="node.schema?.length"
                :schema="node.schema"
                :form="form"
                :initial-data="initialData"
            />
        </component>
    </component>
</template>
