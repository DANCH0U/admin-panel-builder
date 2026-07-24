<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useAdminConfig } from '@/composables/useAdminConfig';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const { adminPath } = useAdminConfig();
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Admin Panel Builder</h1>
            <p class="text-sm text-muted-foreground">
                Build tables, forms, and pages with a PHP schema + shadcn/vue UI.
            </p>
        </div>

        <Card class="max-w-lg">
            <CardHeader>
                <CardTitle>Profile</CardTitle>
                <CardDescription>
                    Update your account name, email, and password.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button variant="secondary" as-child>
                    <Link :href="adminPath('profile')">Open profile</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
