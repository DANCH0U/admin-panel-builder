<script setup lang="ts">
import type { SchemaNodeProps } from '../types';
import { cn } from '@/lib/utils';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    collectDefaults,
    pickFormErrors,
    pickFormInitialData,
    provideSduiForm,
} from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';
import { useId, watch } from 'vue';

const props = defineProps<SchemaNodeProps>();
const page = usePage();
const formId = useId();

const defaults = collectDefaults(props.node.schema ?? []);
const method = String(props.node.method || 'POST').toUpperCase();

// Only this form's fields — never merge the whole page initialData bag.
const formData =
    method === 'DELETE'
        ? { ...defaults }
        : pickFormInitialData(defaults, props.initialData ?? {});

const fieldKeys = Object.keys(formData);

const form =
    props.form && typeof (props.form as any).post === 'function'
        ? props.form
        : useForm(formData);

provideSduiForm(form, formId);

watch(
    () => (page.props as any).errors as Record<string, string | string[]> | undefined,
    (errors) => {
        if (typeof (form as any).setError !== 'function') return;

        const scoped = pickFormErrors(
            Object.fromEntries(fieldKeys.map((k) => [k, true])),
            errors ?? null,
        );

        (form as any).clearErrors?.();
        if (Object.keys(scoped).length) {
            (form as any).setError(scoped);
        }
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
    const allowed = new Set(fieldKeys);

    current.transform((data: Record<string, unknown>) => {
        const next: Record<string, unknown> = {};

        for (const key of allowed) {
            if (key in data) {
                next[key] = data[key];
            }
        }

        for (const key of allowed) {
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
        :id="formId"
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
