<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import { Minus, Plus } from 'lucide-vue-next';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

function current(): number {
    const raw = props.form?.[props.node.name!];
    const n = Number(raw);
    return Number.isFinite(n) ? n : 0;
}

function setValue(next: number) {
    if (!props.node.name) return;
    const min = props.node.min != null ? Number(props.node.min) : null;
    const max = props.node.max != null ? Number(props.node.max) : null;
    let value = next;
    if (min != null && value < min) value = min;
    if (max != null && value > max) value = max;
    props.form[props.node.name] = value;
}

function stepBy(dir: 1 | -1) {
    const step = Number(props.node.step ?? 1) || 1;
    setValue(current() + dir * step);
}
</script>

<template>
    <div class="space-y-2">
        <Label v-if="node.name" :for="node.name">
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>
        <div class="flex items-center gap-2">
            <Button
                v-if="node.controls !== false"
                type="button"
                variant="outline"
                size="icon"
                class="shrink-0"
                :disabled="disabled()"
                @click="stepBy(-1)"
            >
                <Minus class="size-4" />
            </Button>
            <Input
                v-if="node.name"
                :id="node.name"
                class="text-center"
                type="number"
                :min="node.min as number | undefined"
                :max="node.max as number | undefined"
                :step="(node.step as number | undefined) ?? 1"
                :placeholder="node.placeholder"
                :required="node.required"
                :disabled="disabled()"
                :model-value="form[node.name]"
                @update:model-value="setValue(Number($event))"
            />
            <Button
                v-if="node.controls !== false"
                type="button"
                variant="outline"
                size="icon"
                class="shrink-0"
                :disabled="disabled()"
                @click="stepBy(1)"
            >
                <Plus class="size-4" />
            </Button>
        </div>
        <p v-if="node.hint || node.helpText" class="text-xs text-muted-foreground">
            {{ node.hint || node.helpText }}
        </p>
        <p v-if="node.name && form?.errors?.[node.name]" class="text-sm text-destructive">
            {{ form.errors[node.name] }}
        </p>
    </div>
</template>
