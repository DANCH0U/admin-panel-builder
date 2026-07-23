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

    <div class="mb-6 space-y-1">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
            Schema page
        </p>
        <h1 class="text-3xl font-semibold tracking-tight">{{ title || 'Page' }}</h1>
    </div>

    <SchemaRenderer :schema="schema" :initial-data="initialData" />
</template>
