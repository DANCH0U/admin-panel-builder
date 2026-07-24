<script setup lang="ts">
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { isNodeDisabled, optionEntries, type SchemaNodeProps } from '../types';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form);
}
</script>

<template>
    <div class="space-y-2">
        <Label v-if="node.name" :for="node.name">
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>
        <Select
            v-if="node.name"
            :model-value="String(form[node.name] ?? '')"
            :disabled="disabled()"
            @update:model-value="form[node.name!] = $event"
        >
            <SelectTrigger :id="node.name" class="w-full">
                <SelectValue :placeholder="node.placeholder || 'Select...'" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="opt in optionEntries(node.options)"
                    :key="opt.value"
                    :value="opt.value"
                >
                    {{ opt.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
