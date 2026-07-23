<script setup lang="ts">
/**
 * Recursive schema renderer — resolves PHP Schema nodes via the component registry.
 * To add a UI component: register it in ./registry.ts
 */
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { resolveSchemaComponent } from './registry';
import {
    isNodeVisible,
    useSduiForm,
    type InertiaForm,
    type SchemaNode,
} from './types';

const props = defineProps<{
    schema: SchemaNode | SchemaNode[];
    initialData?: Record<string, unknown>;
    form?: InertiaForm | Record<string, unknown>;
    /** Extra classes on the wrapper. Use `contents` inside Flex/Grid so children layout correctly. */
    class?: string;
}>();

const parentForm = useSduiForm();
const activeForm = computed(() => props.form ?? parentForm);

const nodes = computed(() =>
    Array.isArray(props.schema) ? props.schema : props.schema ? [props.schema] : [],
);

function formOf(node?: SchemaNode): any {
    if (node?.type === 'form') return null;
    return activeForm.value ?? {};
}

function isVisible(node: SchemaNode) {
    return isNodeVisible(node, formOf() as Record<string, unknown>);
}

function resolve(type: string) {
    return resolveSchemaComponent(type);
}
</script>

<template>
    <div :class="cn(props.class ?? 'space-y-5')">
        <template v-for="(node, index) in nodes" :key="`${node.type}-${node.name ?? index}`">
            <component
                :is="resolve(node.type)"
                v-if="isVisible(node)"
                :node="node"
                :form="formOf(node)"
                :initial-data="
                    node.type === 'form' && String(node.method || '').toUpperCase() === 'DELETE'
                        ? {}
                        : initialData
                "
            />
        </template>
    </div>
</template>
