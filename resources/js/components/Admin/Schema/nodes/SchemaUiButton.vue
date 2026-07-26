<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { buttonVariant, isNodeDisabled, type SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.form?.processing);
}

function buttonType(): 'button' | 'submit' | 'reset' {
    const raw = String(props.node.type_attr || props.node.buttonType || 'button').toLowerCase();
    if (raw === 'submit' || raw === 'reset') return raw;
    return 'button';
}

function onClick(event: MouseEvent) {
    if (buttonType() === 'submit') {
        return;
    }

    event.preventDefault();

    if (props.node.is_back) {
        window.history.back();
        return;
    }
    if (props.node.url) router.visit(String(props.node.url));
}
</script>

<template>
    <Button
        :type="buttonType()"
        :variant="buttonVariant(node.variant) as any"
        :disabled="disabled()"
        class="rounded-xl"
        @click="onClick"
    >
        {{ node.label || 'Button' }}
    </Button>
</template>
