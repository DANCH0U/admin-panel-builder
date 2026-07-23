<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
import { collectDefaults, provideSduiForm } from '../types';
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import SchemaRenderer from '../SchemaRenderer.vue';

const props = defineProps<SchemaNodeProps>();

const defaults = collectDefaults(props.node.schema ?? []);
const method = String(props.node.method || 'POST').toUpperCase();
const mergeInitial = method !== 'DELETE' ? (props.initialData ?? {}) : {};

const form =
    props.form && typeof (props.form as any).post === 'function'
        ? props.form
        : useForm({
              ...defaults,
              ...mergeInitial,
          });

provideSduiForm(form);

function submit() {
    if (!props.node.action) return;
    const current = form as any;
    const verb = method.toLowerCase();
    const options = { forceFormData: true };
    if (typeof current[verb] === 'function') current[verb](props.node.action, options);
    else if (typeof current.post === 'function') current.post(props.node.action, options);
}
</script>

<template>
    <form
        :class="cn('space-y-6', node.bordered && 'admin-surface p-4 md:p-6')"
        @submit.prevent="submit"
    >
        <SchemaRenderer
            v-if="node.schema?.length"
            :schema="node.schema"
            :form="form"
            :initial-data="initialData"
        />
    </form>
</template>
