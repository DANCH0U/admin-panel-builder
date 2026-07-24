<script setup lang="ts">
import { computed } from 'vue';
import { fieldError } from '../types';

const props = defineProps<{
    form: any;
    name?: string | null;
    /** Extra error keys to check (e.g. image_file for FileInput) */
    aliases?: string[];
}>();

const message = computed(() =>
    fieldError(props.form, props.name, ...(props.aliases ?? [])),
);
</script>

<template>
    <p v-if="message" class="text-sm text-destructive">{{ message }}</p>
</template>
