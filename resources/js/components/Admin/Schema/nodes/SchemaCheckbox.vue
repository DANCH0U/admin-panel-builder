<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, type SchemaNodeProps } from '../types';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}
</script>

<template>
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
</template>
