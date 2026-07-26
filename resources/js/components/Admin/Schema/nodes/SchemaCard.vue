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
                    ? 'overflow-hidden rounded-2xl border border-border bg-card text-card-foreground shadow-sm'
                    : 'space-y-4',
            )
        "
    >
        <CardHeader
            v-if="node.bordered && (node.label || node.helpText)"
            class="space-y-1.5 px-5 pb-3 pt-5"
        >
            <CardTitle v-if="node.label" class="text-base leading-snug">{{ node.label }}</CardTitle>
            <CardDescription v-if="node.helpText" class="text-sm leading-relaxed">
                {{ node.helpText }}
            </CardDescription>
        </CardHeader>
        <div v-else-if="node.label || node.helpText" class="space-y-1.5">
            <h3 v-if="node.label" class="text-base font-semibold leading-snug tracking-tight">
                {{ node.label }}
            </h3>
            <p v-if="node.helpText" class="text-sm leading-relaxed text-muted-foreground">
                {{ node.helpText }}
            </p>
        </div>

        <component
            :is="node.bordered ? CardContent : 'div'"
            :class="
                node.bordered
                    ? cn('px-5 pb-5', node.label || node.helpText ? 'pt-1' : 'pt-5')
                    : 'space-y-4'
            "
        >
            <SchemaRenderer
                v-if="node.schema?.length"
                :schema="node.schema"
                :form="form"
                :initial-data="initialData"
                class="space-y-5"
            />
        </component>
    </component>
</template>
