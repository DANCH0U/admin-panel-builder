<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { buttonVariant, isNodeDisabled, useSduiFormId, type SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();
const formId = useSduiFormId();

const isMdUp = ref(
    typeof window !== 'undefined' ? window.matchMedia('(min-width: 768px)').matches : true,
);

function syncBreakpoint() {
    isMdUp.value = window.matchMedia('(min-width: 768px)').matches;
}

onMounted(() => {
    syncBreakpoint();
    const mq = window.matchMedia('(min-width: 768px)');
    mq.addEventListener('change', syncBreakpoint);
    onUnmounted(() => mq.removeEventListener('change', syncBreakpoint));
});

const showOnBottomBar = computed(() => Boolean(props.node.showOnBottomBar));
const teleportToBar = computed(() => showOnBottomBar.value && !isMdUp.value);

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
        // Teleported outside <form> — submit only this form by id.
        if (teleportToBar.value && formId) {
            event.preventDefault();
            const el = document.getElementById(formId);
            if (el instanceof HTMLFormElement) {
                el.requestSubmit();
            }
        }
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
    <Teleport to="#admin-mobile-bottom-bar" :disabled="!teleportToBar">
        <Button
            :type="buttonType()"
            :variant="buttonVariant(node.variant) as any"
            :disabled="disabled()"
            class="rounded-xl"
            @click="onClick"
        >
            {{ node.label || 'Button' }}
        </Button>
    </Teleport>
</template>
