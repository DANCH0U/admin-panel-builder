<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form);
}
</script>

<template>
    <div class="space-y-1.5">
        <div class="flex items-center justify-between gap-4 py-1">
            <div class="space-y-0.5">
                <Label v-if="node.name" :for="node.name">{{ node.label || node.name }}</Label>
                <p v-if="node.helpText" class="text-xs text-muted-foreground">{{ node.helpText }}</p>
            </div>
            <Switch
                v-if="node.name"
                :id="node.name"
                :model-value="Boolean(form[node.name])"
                :disabled="disabled()"
                @update:model-value="form[node.name!] = $event"
            />
        </div>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
