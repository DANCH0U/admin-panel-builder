<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import { ImagePlus, Trash2, Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();
const inputRef = ref<HTMLInputElement | null>(null);
const localPreview = ref<string | null>(null);

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

const fileKey = computed(() => `${props.node.name}_file`);
const currentUrl = computed(() => {
    if (localPreview.value) return localPreview.value;
    const value = props.form?.[props.node.name!];
    return typeof value === 'string' && value ? value : null;
});

function pick() {
    inputRef.value?.click();
}

function onFile(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file || !props.node.name) return;

    props.form[fileKey.value] = file;

    if (props.node.image) {
        localPreview.value = URL.createObjectURL(file);
    }
}

function clear() {
    if (!props.node.name) return;
    props.form[props.node.name] = '';
    props.form[fileKey.value] = null;
    localPreview.value = null;
    if (inputRef.value) inputRef.value.value = '';
}
</script>

<template>
    <div class="space-y-2">
        <Label>
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>

        <div
            v-if="node.image"
            class="relative flex min-h-40 items-center justify-center overflow-hidden rounded-lg border border-dashed bg-muted/30"
        >
            <img
                v-if="currentUrl"
                :src="currentUrl"
                alt=""
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div
                v-if="!currentUrl"
                class="flex flex-col items-center gap-2 p-6 text-center text-sm text-muted-foreground"
            >
                <ImagePlus class="size-8 opacity-60" />
                <span>Drop or choose an image</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 flex gap-2 bg-background/80 p-2 backdrop-blur">
                <Button type="button" size="sm" variant="secondary" :disabled="disabled()" @click="pick">
                    <Upload class="size-4" />
                    Choose
                </Button>
                <Button
                    v-if="currentUrl"
                    type="button"
                    size="sm"
                    variant="outline"
                    :disabled="disabled()"
                    @click="clear"
                >
                    <Trash2 class="size-4" />
                    Remove
                </Button>
            </div>
        </div>

        <div v-else class="flex items-center gap-2">
            <Button type="button" variant="outline" :disabled="disabled()" @click="pick">
                <Upload class="size-4" />
                Choose file
            </Button>
            <span class="truncate text-sm text-muted-foreground">
                {{ form[fileKey] instanceof File ? form[fileKey].name : currentUrl || 'No file selected' }}
            </span>
            <Button
                v-if="currentUrl || form[fileKey]"
                type="button"
                variant="ghost"
                size="icon-sm"
                :disabled="disabled()"
                @click="clear"
            >
                <Trash2 class="size-4" />
            </Button>
        </div>

        <input
            ref="inputRef"
            type="file"
            class="hidden"
            :accept="(node.accept as string) || (node.image ? 'image/*' : undefined)"
            :disabled="disabled()"
            @change="onFile"
        />

        <p v-if="node.hint || node.helpText" class="text-xs text-muted-foreground">
            {{ node.hint || node.helpText }}
        </p>
        <SchemaFieldError
            :form="form"
            :name="node.name"
            :aliases="node.name ? [`${node.name}_file`] : []"
        />
    </div>
</template>
