<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<SchemaNodeProps>();

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

function items(): string[] {
    const value = props.form?.[props.node.name!];
    return Array.isArray(value) ? value.map(String) : [];
}

function setItems(next: string[]) {
    if (!props.node.name) return;
    props.form[props.node.name] = next;
}

function updateAt(index: number, value: string) {
    const next = [...items()];
    next[index] = value;
    setItems(next);
}

function add() {
    const max = props.node.max != null ? Number(props.node.max) : null;
    const next = [...items()];
    if (max != null && next.length >= max) return;
    next.push('');
    setItems(next);
}

function removeAt(index: number) {
    setItems(items().filter((_, i) => i !== index));
}
</script>

<template>
    <div class="space-y-3">
        <div>
            <Label>{{ node.label || node.name }}</Label>
            <p v-if="node.hint || node.helpText" class="text-xs text-muted-foreground">
                {{ node.hint || node.helpText }}
            </p>
        </div>

        <div class="space-y-2">
            <div
                v-for="(item, index) in items()"
                :key="index"
                class="flex items-center gap-2"
            >
                <Input
                    :model-value="item"
                    :placeholder="node.placeholder || 'Item…'"
                    :disabled="disabled()"
                    @update:model-value="updateAt(index, String($event ?? ''))"
                />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    :disabled="disabled()"
                    @click="removeAt(index)"
                >
                    <Trash2 class="size-4" />
                </Button>
            </div>
        </div>

        <Button type="button" variant="outline" size="sm" :disabled="disabled()" @click="add">
            <Plus class="size-4" />
            {{ node.addLabel || 'Add item' }}
        </Button>

        <p v-if="node.name && form?.errors?.[node.name]" class="text-sm text-destructive">
            {{ form.errors[node.name] }}
        </p>
    </div>
</template>
