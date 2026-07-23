<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { isNodeDisabled, type SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form);
}
</script>

<template>
    <div class="flex items-center justify-between gap-4 py-1">
        <div class="space-y-0.5">
            <Label v-if="node.name" :for="node.name">{{ node.label || node.name }}</Label>
            <p v-if="node.helpText" class="text-xs text-muted-foreground">{{ node.helpText }}</p>
        </div>
        <Switch
            v-if="node.name"
            :id="node.name"
            :checked="Boolean(form[node.name])"
            :disabled="disabled()"
            @update:checked="form[node.name!] = $event"
        />
    </div>
</template>
