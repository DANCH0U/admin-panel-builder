<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import { computed } from 'vue';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

const text = computed({
    get() {
        const value = props.form?.[props.node.name!];
        if (value == null) return '';
        if (typeof value === 'string') return value;
        try {
            return JSON.stringify(value, null, props.node.pretty === false ? 0 : 2);
        } catch {
            return String(value);
        }
    },
    set(next: string) {
        if (!props.node.name) return;
        props.form[props.node.name] = next;
    },
});

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

function formatJson() {
    try {
        const parsed = JSON.parse(text.value || '{}');
        text.value = JSON.stringify(parsed, null, 2);
    } catch {
        // leave invalid JSON for server validation
    }
}
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between gap-2">
            <Label v-if="node.name" :for="node.name">
                {{ node.label || node.name }}
                <span v-if="node.required" class="text-destructive">*</span>
            </Label>
            <Button type="button" variant="ghost" size="sm" :disabled="disabled()" @click="formatJson">
                Format
            </Button>
        </div>
        <Textarea
            v-if="node.name"
            :id="node.name"
            v-model="text"
            class="min-h-48 rounded-xl font-mono text-xs leading-relaxed"
            :rows="Number(node.rows || 12)"
            :placeholder="node.placeholder"
            :required="node.required"
            :disabled="disabled()"
        />
        <p v-if="node.hint || node.helpText" class="text-xs text-muted-foreground">
            {{ node.hint || node.helpText }}
        </p>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
