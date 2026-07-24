<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, optionEntries, type SchemaNodeProps } from '../types';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form);
}

function selected(): string[] {
    const value = props.form?.[props.node.name!];
    return Array.isArray(value) ? value.map(String) : [];
}

function toggle(value: string, checked: boolean | 'indeterminate') {
    if (!props.node.name) return;
    const current = selected();
    props.form[props.node.name] =
        checked === true
            ? Array.from(new Set([...current, value]))
            : current.filter((item) => item !== value);
}
</script>

<template>
    <div class="space-y-3">
        <div>
            <Label>{{ node.label || node.name }}</Label>
            <p v-if="node.helpText || node.placeholder" class="text-xs text-muted-foreground">
                {{ node.helpText || node.placeholder }}
            </p>
        </div>
        <div class="grid gap-2 sm:grid-cols-2">
            <label
                v-for="opt in optionEntries(node.options)"
                :key="opt.value"
                class="flex items-center gap-2 rounded-md px-1 py-1 text-sm"
            >
                <Checkbox
                    :checked="selected().includes(opt.value)"
                    :disabled="disabled()"
                    @update:checked="(v) => toggle(opt.value, v)"
                />
                <span>{{ opt.label }}</span>
            </label>
        </div>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
