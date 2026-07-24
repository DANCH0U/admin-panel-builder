<script setup lang="ts">
import SchemaRenderer from '@/components/Admin/Schema/SchemaRenderer.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

defineProps<{
    schema: Array<Record<string, unknown>>;
    title?: string | null;
    initialData?: Record<string, unknown>;
}>();
</script>

<template>
    <Head :title="title || 'Page'" />

    <div class="mb-4 space-y-1">
        <h1 class="text-2xl font-semibold leading-tight tracking-tight">{{ title || 'Page' }}</h1>
    </div>

    <SchemaRenderer :schema="schema" :initial-data="initialData" class="space-y-4" />
</template>
