<script setup lang="ts">
import DataTable from '@/components/Admin/Tables/DataTable.vue';
import { Button } from '@/components/ui/button';
import { useAdminConfig } from '@/composables/useAdminConfig';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

defineProps<{
    resource: Record<string, unknown>;
}>();

const { adminPath } = useAdminConfig();
</script>

<template>
    <Head title="Tests" />

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                Resource
            </p>
            <h1 class="text-3xl font-semibold tracking-tight">Tests</h1>
            <p class="max-w-xl text-sm text-muted-foreground">
                Sample table resource powered by the DataGrid engine.
            </p>
        </div>
        <Button class="rounded-xl px-4" as-child>
            <Link :href="adminPath('tests/create')">Add test</Link>
        </Button>
    </div>

    <div class="admin-surface p-4 md:p-5">
        <DataTable :dataset="(resource as any)" />
    </div>
</template>
