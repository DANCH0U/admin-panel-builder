<script setup lang="ts">
import DataTable from '@/components/Admin/Tables/DataTable.vue';
import SchemaRenderer from '@/components/Admin/Schema/SchemaRenderer.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

defineProps<{
    resource: Record<string, unknown>;
    title?: string | null;
    header?: Array<Record<string, unknown>>;
}>();
</script>

<template>

    <Head :title="title || 'Admin Panel'" />

    <div v-if="header?.length">
        <SchemaRenderer :schema="header" />
    </div>

    <div class="admin-surface p-4 md:p-5">
        <DataTable :dataset="(resource as any)" />
    </div>
</template>
