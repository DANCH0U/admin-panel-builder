<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';
import { computed } from 'vue';

const { locale, setLocale } = useI18n();
setLocale();
const localization: Record<string, { font: string }> = {
    ar: { font: '"Cairo" , "Outfit", serif' },
    en: { font: '"Outfit" , "Cairo", serif' },
};
const font = computed(() => localization[locale.value].font);
</script>

<template>
    <div>
        <slot />
    </div>
</template>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Outfit:wght@100..900&display=swap');

.cl-app {
    max-width: 100%;
    width: 100%;
}

.cl-app * {
    box-sizing: border-box;
    font-family: v-bind(font) !important;
    font-optical-sizing: auto;
}
</style>
