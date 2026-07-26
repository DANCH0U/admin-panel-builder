<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import SchemaRenderer from '../SchemaRenderer.vue';
import { collectDefaults, isNodeDisabled, type SchemaNode, type SchemaNodeProps } from '../types';
import { ChevronDown, ChevronUp, GripVertical, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();
const collapsed = ref<Record<number, boolean>>({});

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

const blockSchema = computed(() => (props.node.schema ?? []) as SchemaNode[]);

const items = computed<Record<string, unknown>[]>({
    get() {
        const value = props.form?.[props.node.name!];
        return Array.isArray(value) ? value : [];
    },
    set(next) {
        if (!props.node.name) return;
        props.form[props.node.name] = next;
    },
});

function emptyItem(): Record<string, unknown> {
    return collectDefaults(blockSchema.value);
}

function ensureDefaults() {
    if (!props.node.name) return;
    if (!Array.isArray(props.form[props.node.name])) {
        const count = Number(props.node.defaultItems ?? 1);
        props.form[props.node.name] = Array.from({ length: count }, () => emptyItem());
    }
}

ensureDefaults();

function canAdd() {
    if (props.node.addable === false) return false;
    const max = props.node.maxItems != null ? Number(props.node.maxItems) : null;
    return max == null || items.value.length < max;
}

function canRemove() {
    if (props.node.deletable === false) return false;
    const min = Number(props.node.minItems ?? 0);
    return items.value.length > min;
}

function addItem() {
    if (!canAdd() || disabled()) return;
    items.value = [...items.value, emptyItem()];
}

function removeItem(index: number) {
    if (!canRemove() || disabled()) return;
    items.value = items.value.filter((_, i) => i !== index);
}

function moveItem(index: number, dir: -1 | 1) {
    if (props.node.reorderable === false || disabled()) return;
    const next = [...items.value];
    const target = index + dir;
    if (target < 0 || target >= next.length) return;
    const [row] = next.splice(index, 1);
    next.splice(target, 0, row);
    items.value = next;
}

function isCollapsed(index: number) {
    if (!props.node.collapsible) return false;
    if (collapsed.value[index] != null) return collapsed.value[index];
    return Boolean(props.node.collapsed);
}

function toggleCollapsed(index: number) {
    collapsed.value[index] = !isCollapsed(index);
}

function itemTitle(index: number) {
    const label = String(props.node.itemLabel || 'Item');
    const row = items.value[index] || {};
    const title = row.title || row.name || row.label;
    return title ? `${label} ${index + 1} · ${title}` : `${label} ${index + 1}`;
}

function rowErrors(index: number): string | undefined {
    const errors = (props.form as any)?.errors;
    if (!errors || !props.node.name) return undefined;
    const prefix = `${props.node.name}.${index}`;
    const hit = Object.entries(errors).find(([key]) => key === props.node.name || key.startsWith(prefix));
    return hit ? String(hit[1]) : undefined;
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <Label class="text-sm font-medium">{{ node.label || node.name }}</Label>
                <p v-if="node.hint || node.helpText" class="mt-1 text-xs text-muted-foreground">
                    {{ node.hint || node.helpText }}
                </p>
            </div>
            <Button
                v-if="canAdd()"
                type="button"
                size="sm"
                variant="secondary"
                class="shrink-0"
                :disabled="disabled()"
                @click="addItem"
            >
                <Plus class="size-4" />
                {{ node.addLabel || 'Add item' }}
            </Button>
        </div>

        <div v-if="!items.length" class="rounded-2xl border border-dashed bg-muted/20 px-4 py-10 text-center">
            <p class="text-sm text-muted-foreground">No items yet.</p>
            <Button
                v-if="canAdd()"
                type="button"
                size="sm"
                class="mt-3"
                :disabled="disabled()"
                @click="addItem"
            >
                <Plus class="size-4" />
                {{ node.addLabel || 'Add item' }}
            </Button>
        </div>

        <div class="space-y-3">
            <div
                v-for="(row, index) in items"
                :key="index"
                :class="
                    cn(
                        'overflow-hidden rounded-2xl border border-border bg-card text-card-foreground shadow-sm',
                        'ring-1 ring-black/3 dark:ring-white/5',
                    )
                "
            >
                <div class="flex items-center gap-2 border-b bg-muted/30 px-3 py-2.5">
                    <GripVertical
                        v-if="node.reorderable"
                        class="size-4 shrink-0 text-muted-foreground"
                    />
                    <button
                        v-if="node.collapsible"
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-2 text-left text-sm font-medium"
                        @click="toggleCollapsed(index)"
                    >
                        <component
                            :is="isCollapsed(index) ? ChevronDown : ChevronUp"
                            class="size-4 text-muted-foreground"
                        />
                        <span class="truncate">{{ itemTitle(index) }}</span>
                    </button>
                    <p v-else class="min-w-0 flex-1 truncate text-sm font-medium">
                        {{ itemTitle(index) }}
                    </p>

                    <div class="flex items-center gap-1">
                        <Button
                            v-if="node.reorderable"
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            :disabled="disabled() || index === 0"
                            @click="moveItem(index, -1)"
                        >
                            <ChevronUp class="size-4" />
                        </Button>
                        <Button
                            v-if="node.reorderable"
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            :disabled="disabled() || index === items.length - 1"
                            @click="moveItem(index, 1)"
                        >
                            <ChevronDown class="size-4" />
                        </Button>
                        <Button
                            v-if="canRemove()"
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            class="text-destructive hover:text-destructive"
                            :disabled="disabled()"
                            @click="removeItem(index)"
                        >
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                </div>

                <div v-show="!isCollapsed(index)" class="p-4">
                    <SchemaRenderer :schema="blockSchema" :form="row" />
                    <p v-if="rowErrors(index)" class="mt-2 text-sm text-destructive">
                        {{ rowErrors(index) }}
                    </p>
                </div>
            </div>
        </div>

        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
