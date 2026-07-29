<script setup lang="ts">
import {
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
} from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';
import { Eye, Pencil, Trash2 } from 'lucide-vue-next';
import type { Component } from 'vue';

export type ActionNode = {
    name: string;
    label: string;
    icon?: string | null;
    type?: string;
    url?: string | null;
    api?: string | null;
    method?: string;
    requiresConfirmation?: boolean;
    confirmTitle?: string | null;
    confirmText?: string | null;
    confirmButton?: string | null;
    disabled?: boolean;
    items?: ActionNode[];
};

const props = defineProps<{ actions: ActionNode[] }>();

const emit = defineEmits<{ (e: 'select', action: ActionNode): void }>();

/** Guessed icon for actions that don't declare one. */
function fallbackIcon(action: ActionNode): Component | null {
    const name = (action.name || '').toLowerCase();
    if (name.includes('view') || name.includes('show')) return Eye;
    if (name.includes('edit') || name.includes('update')) return Pencil;
    if (name.includes('delete') || name.includes('destroy') || action.type === 'destructive') {
        return Trash2;
    }
    return null;
}

function isGroup(action: ActionNode) {
    return Boolean(action.items?.length);
}

function needsSeparator(index: number) {
    return index > 0 && props.actions[index]?.type === 'destructive';
}
</script>

<template>
    <template v-for="(action, index) in actions" :key="action.name || index">
        <DropdownMenuSeparator v-if="needsSeparator(index)" class="my-1" />

        <DropdownMenuSub v-if="isGroup(action)">
            <DropdownMenuSubTrigger class="cursor-pointer gap-2 rounded-lg px-2.5 py-2">
                <Icon v-if="action.icon" :icon="action.icon" class="size-4 opacity-70" />
                <component
                    :is="fallbackIcon(action)"
                    v-else-if="fallbackIcon(action)"
                    class="size-4 opacity-70"
                />
                <span>{{ action.label }}</span>
            </DropdownMenuSubTrigger>
            <DropdownMenuSubContent class="w-44 rounded-xl p-1.5">
                <DataTableActionItems
                    :actions="action.items || []"
                    @select="emit('select', $event)"
                />
            </DropdownMenuSubContent>
        </DropdownMenuSub>

        <DropdownMenuItem
            v-else
            :disabled="action.disabled"
            class="cursor-pointer gap-2 rounded-lg px-2.5 py-2"
            :class="{
                'text-destructive focus:bg-destructive/10 focus:text-destructive':
                    action.type === 'destructive',
            }"
            @click="emit('select', action)"
        >
            <Icon v-if="action.icon" :icon="action.icon" class="size-4 opacity-70" />
            <component
                :is="fallbackIcon(action)"
                v-else-if="fallbackIcon(action)"
                class="size-4 opacity-70"
            />
            <span>{{ action.label }}</span>
        </DropdownMenuItem>
    </template>
</template>
