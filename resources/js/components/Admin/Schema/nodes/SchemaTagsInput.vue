<script setup lang="ts">
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { X } from 'lucide-vue-next';
import { hasFieldError, isNodeDisabled, type SchemaNodeProps } from '../types';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();

const draft = ref('');
const focused = ref(false);
const inputEl = ref<HTMLInputElement | null>(null);

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

const tags = computed({
    get(): string[] {
        const value = props.node.name ? props.form?.[props.node.name] : null;
        return Array.isArray(value) ? value.map(String) : [];
    },
    set(next: string[]) {
        if (!props.node.name) return;
        props.form[props.node.name] = next;
    },
});

const max = computed(() => (props.node.max != null ? Number(props.node.max) : null));
const allowDuplicates = computed(() => Boolean(props.node.allowDuplicates));
const atMax = computed(() => max.value != null && tags.value.length >= max.value);

const suggestions = computed(() => {
    const raw = props.node.suggestions;
    if (!Array.isArray(raw)) return [] as string[];
    return raw.map(String).filter((s) => s.trim() !== '');
});

const visibleSuggestions = computed(() => {
    const q = draft.value.trim().toLowerCase();
    return suggestions.value.filter((s) => {
        if (!allowDuplicates.value && tags.value.some((t) => t.toLowerCase() === s.toLowerCase())) {
            return false;
        }
        if (!q) return true;
        return s.toLowerCase().includes(q);
    });
});

function normalize(raw: string): string {
    return raw.trim().replace(/\s+/g, ' ');
}

function canAdd(tag: string): boolean {
    if (!tag || disabled() || atMax.value) return false;
    if (!allowDuplicates.value && tags.value.some((t) => t.toLowerCase() === tag.toLowerCase())) {
        return false;
    }
    return true;
}

function addTag(raw: string) {
    const tag = normalize(raw);
    if (!canAdd(tag)) {
        draft.value = '';
        return;
    }
    tags.value = [...tags.value, tag];
    draft.value = '';
}

function addMany(raw: string) {
    const parts = raw
        .split(/[,;\n]+/)
        .map(normalize)
        .filter(Boolean);
    for (const part of parts) {
        if (atMax.value) break;
        if (canAdd(part)) {
            tags.value = [...tags.value, part];
        }
    }
    draft.value = '';
}

function removeAt(index: number) {
    if (disabled()) return;
    tags.value = tags.value.filter((_, i) => i !== index);
}

function onKeydown(event: KeyboardEvent) {
    if (disabled()) return;

    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addTag(draft.value);
        return;
    }

    if (event.key === 'Backspace' && draft.value === '' && tags.value.length) {
        event.preventDefault();
        removeAt(tags.value.length - 1);
    }
}

function onPaste(event: ClipboardEvent) {
    const text = event.clipboardData?.getData('text') ?? '';
    if (!/[,;\n]/.test(text)) return;
    event.preventDefault();
    addMany(`${draft.value}${text}`);
}

function onBlur() {
    focused.value = false;
    if (draft.value.trim()) addTag(draft.value);
}

function focusInput() {
    inputEl.value?.focus();
}
</script>

<template>
    <div class="space-y-1.5" :style="node.width ? { width: node.width } : undefined">
        <Label v-if="node.name" :for="node.name">
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>

        <div
            :class="
                cn(
                    'flex min-h-10 w-full flex-wrap items-center gap-1.5 rounded-md border border-input bg-transparent px-2.5 py-1.5 text-sm shadow-xs transition-[color,box-shadow]',
                    'focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]',
                    hasFieldError(form, node.name) && 'border-destructive',
                    disabled() && 'cursor-not-allowed opacity-50',
                )
            "
            @click="focusInput"
        >
            <Badge
                v-for="(tag, index) in tags"
                :key="`${tag}-${index}`"
                variant="secondary"
                class="gap-1 pr-1 font-normal"
            >
                <span class="max-w-[12rem] truncate">{{ tag }}</span>
                <button
                    type="button"
                    class="rounded-sm p-0.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                    :disabled="disabled()"
                    :aria-label="`Remove ${tag}`"
                    @click.stop="removeAt(index)"
                >
                    <X class="size-3" />
                </button>
            </Badge>

            <input
                v-if="node.name"
                :id="node.name"
                ref="inputEl"
                v-model="draft"
                type="text"
                class="min-w-[8rem] flex-1 border-0 bg-transparent py-0.5 outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed"
                :placeholder="tags.length ? '' : node.placeholder || 'Type and press Enter…'"
                :disabled="disabled() || atMax"
                :aria-invalid="hasFieldError(form, node.name)"
                @keydown="onKeydown"
                @paste="onPaste"
                @focus="focused = true"
                @blur="onBlur"
            />
        </div>

        <div
            v-if="visibleSuggestions.length && (focused || draft)"
            class="flex flex-wrap gap-1.5"
        >
            <button
                v-for="suggestion in visibleSuggestions.slice(0, 12)"
                :key="suggestion"
                type="button"
                class="rounded-md border border-dashed border-border px-2 py-0.5 text-xs text-muted-foreground transition-colors hover:border-primary hover:text-foreground"
                :disabled="disabled() || atMax"
                @mousedown.prevent="addTag(suggestion)"
            >
                {{ suggestion }}
            </button>
        </div>

        <p v-if="node.hint || node.helpText" class="text-xs text-muted-foreground">
            {{ node.hint || node.helpText }}
        </p>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
