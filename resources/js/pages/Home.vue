<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: DefaultLayout });

</script>

<template>
    <Head title="Dashboard" />

    <div class="flex justify-center items-center h-screen">


        <Card class="max-w-lg">
            <CardHeader>
                <CardTitle>Admin Panel Builder</CardTitle>
                <CardDescription>
                    Build tables, forms, and pages with a PHP schema + shadcn/vue UI.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button variant="secondary" as-child>
                    <Link href="/login">Login</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
