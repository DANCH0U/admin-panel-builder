<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { hasFieldError, isNodeDisabled, type SchemaNodeProps } from '../types';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form);
}
</script>

<template>
    <div class="space-y-1.5">
        <Label v-if="node.name" :for="node.name">
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>
        <Textarea
            v-if="node.name"
            :id="node.name"
            v-model="form[node.name]"
            :placeholder="node.placeholder"
            :required="node.required"
            :disabled="disabled()"
            :aria-invalid="hasFieldError(form, node.name)"
            :class="hasFieldError(form, node.name) ? 'border-destructive' : undefined"
        />
        <p v-if="node.helpText" class="text-xs text-muted-foreground">{{ node.helpText }}</p>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
