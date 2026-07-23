<script setup lang="ts">
import NotificationHost from '@/components/Admin/NotificationHost.vue';
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <NotificationHost />
        <slot />
    </div>
</template>
