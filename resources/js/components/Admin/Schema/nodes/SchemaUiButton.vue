<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { buttonVariant, isNodeDisabled, type SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.form?.processing);
}

function onClick() {
    if (props.node.is_back) {
        window.history.back();
        return;
    }
    if (props.node.url) router.visit(props.node.url);
}
</script>

<template>
    <Button
        :type="(node.type_attr || node.buttonType || 'button') as any"
        :variant="buttonVariant(node.variant) as any"
        :disabled="disabled()"
        @click="onClick"
    >
        {{ node.label || 'Button' }}
    </Button>
</template>
