<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}
</script>

<template>
    <div class="space-y-1.5">
        <div class="flex items-center gap-2">
            <Checkbox
                v-if="node.name"
                :id="node.name"
                class="rounded-[4px]"
                :checked="Boolean(form[node.name])"
                :disabled="disabled()"
                @update:checked="form[node.name!] = $event"
            />
            <Label v-if="node.name" :for="node.name" class="font-normal">
                {{ node.label || node.name }}
            </Label>
        </div>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
