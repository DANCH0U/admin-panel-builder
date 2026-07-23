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

        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Tables</CardTitle>
                    <CardDescription>
                        Define columns, filters, tabs, and actions in PHP. Render with DataTable.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button as-child>
                        <Link :href="adminPath('tests')">Open tests sample</Link>
                    </Button>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Settings</CardTitle>
                    <CardDescription>
                        Panel branding (name, logo) stored per panel in the database.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Button variant="secondary" as-child>
                        <Link :href="adminPath('settings')">Open settings</Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
