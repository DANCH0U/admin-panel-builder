<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-2">
        <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
        <p class="text-sm text-muted-foreground">
            Use <code class="text-xs">php artisan make:admin-resource</code> /
            <code class="text-xs">make:admin-page</code> to add resources and pages.
        </p>
    </div>
</template>
