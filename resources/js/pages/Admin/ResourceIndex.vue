<script setup lang="ts">
import DataTable from '@/components/Admin/Tables/DataTable.vue';
import { Button } from '@/components/ui/button';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

defineProps<{
    resource: Record<string, unknown>;
    title: string;
    description?: string | null;
    createUrl?: string | null;
    createLabel?: string | null;
}>();
</script>

<template>
    <Head :title="title" />

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
            <p v-if="description" class="max-w-xl text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <Button v-if="createUrl" class="rounded-xl px-4" as-child>
            <Link :href="createUrl">{{ createLabel || 'Add' }}</Link>
        </Button>
    </div>

    <div class="admin-surface p-4 md:p-5">
        <DataTable :dataset="(resource as any)" />
    </div>
</template>
