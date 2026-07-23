<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, type SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form);
}
</script>

<template>
    <div class="space-y-2" :style="node.width ? { width: node.width } : undefined">
        <Label v-if="node.name" :for="node.name">
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>
        <Input
            v-if="node.name"
            :id="node.name"
            v-model="form[node.name]"
                        :type="(node.inputType as string) || (node.props?.type as string) || 'text'"
            :placeholder="node.placeholder"
            :required="node.required"
            :disabled="disabled()"
        />
        <p v-if="node.helpText" class="text-xs text-muted-foreground">{{ node.helpText }}</p>
        <p v-if="node.name && form?.errors?.[node.name]" class="text-sm text-destructive">
            {{ form.errors[node.name] }}
        </p>
    </div>
</template>
