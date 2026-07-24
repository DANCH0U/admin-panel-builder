<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
import { cn } from '@/lib/utils';
import { useForm, usePage } from '@inertiajs/vue3';
import { collectDefaults, provideSduiForm } from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';
import { watch } from 'vue';

const props = defineProps<SchemaNodeProps>();
const page = usePage();

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

watch(
    () => (page.props as any).errors as Record<string, string | string[]> | undefined,
    (errors) => {
        if (!errors || !Object.keys(errors).length) return;
        if (typeof (form as any).setError !== 'function') return;
        (form as any).clearErrors?.();
        (form as any).setError(errors);
    },
    { deep: true, immediate: true },
);

function submit() {
    if (!props.node.action) return;
    const current = form as any;
    const verb = method.toLowerCase();
    const options = { forceFormData: true };
    // PHP does not parse multipart PUT/PATCH bodies — spoof via POST + _method
    const spoofAsPost = verb === 'put' || verb === 'patch';

    current.transform((data: Record<string, unknown>) => {
        const next = { ...data };
        for (const key of Object.keys(current)) {
            if (key.endsWith('_file') && current[key] instanceof File) {
                next[key] = current[key];
            }
        }
        if (spoofAsPost) {
            next._method = verb.toUpperCase();
        }
        return next;
    });

    if (spoofAsPost) {
        current.post(props.node.action, options);
    } else if (typeof current[verb] === 'function') {
        current[verb](props.node.action, options);
    } else if (typeof current.post === 'function') {
        current.post(props.node.action, options);
    }
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
            class="space-y-4"
        />
    </form>
</template>
