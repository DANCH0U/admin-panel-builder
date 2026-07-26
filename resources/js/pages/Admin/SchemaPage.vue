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
    <SchemaRenderer :schema="schema" :initial-data="initialData"    />
</template>
