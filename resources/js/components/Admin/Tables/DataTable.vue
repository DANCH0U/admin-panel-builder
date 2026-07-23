<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/vue3';
import {
    ChevronDown,
    Eye,
    Filter,
    MoreHorizontal,
    Pencil,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref, watch, type Component } from 'vue';

type Column = {
    name: string;
    label?: string;
    type?: string;
    hidden?: boolean;
    colors?: Record<string, string>;
};

type TableAction = {
    name: string;
    label: string;
    icon?: string;
    type?: string;
    url?: string | null;
    api?: string | null;
    method?: string;
    requiresConfirmation?: boolean;
    confirmTitle?: string | null;
    confirmText?: string | null;
    confirmButton?: string | null;
    disabled?: boolean;
    items?: TableAction[];
};

type BulkAction = {
    name: string;
    label: string;
    icon?: string;
    type?: string;
    requiresConfirmation?: boolean;
    confirmTitle?: string | null;
    confirmText?: string | null;
    confirmButton?: string | null;
};

type Dataset = {
    records?: Array<Record<string, any>>;
    current_page?: number;
    total_pages?: number;
    total_records?: number;
    schema?: {
        tabs?: Array<{
            value: string;
            label: string;
            count?: number | null;
            color?: string;
            badge_color?: string;
        }>;
        search?: { placeholder?: string } | null;
        columns?: Column[];
        filters?: Array<{
            name: string;
            label?: string;
            type?: string;
            options?:
                | Record<string, string>
                | Array<{ value: string; label: string }>;
        }>;
        bulk_actions?: BulkAction[];
        settings?: {
            record_selection?: boolean;
            selection_column?: string;
            bulk_url?: string | null;
        };
    };
};

const props = defineProps<{
    dataset: Dataset;
    reloadOnly?: string[];
}>();

const records = computed(() => props.dataset?.records ?? []);
const schema = computed(() => props.dataset?.schema ?? {});
const columns = computed(
    () => (schema.value.columns ?? []).filter((c) => !c.hidden && c.type !== 'table_actions'),
);
const selectionColumn = computed(
    () => schema.value.settings?.selection_column ?? 'id',
);
const bulkActions = computed(() => schema.value.bulk_actions ?? []);
const bulkUrl = computed(() => schema.value.settings?.bulk_url || null);
/** Selection column + bulk bar: on when configured, or automatically when bulk actions exist. */
const selectable = computed(() => {
    const settings = schema.value.settings ?? {};
    if (settings.record_selection === false) return false;
    if (settings.record_selection) return true;
    return bulkActions.value.length > 0;
});

const search = ref('');
const activeTab = ref(schema.value.tabs?.[0]?.value ?? 'all');
const filters = ref<Record<string, string>>({});
const selected = ref<Array<string | number>>([]);
const loading = ref(false);

const confirmOpen = ref(false);
const pendingAction = ref<TableAction | null>(null);
const pendingBulk = ref<BulkAction | null>(null);

watch(
    () => schema.value.tabs,
    (tabs) => {
        if (tabs?.length && !tabs.find((t) => t.value === activeTab.value)) {
            activeTab.value = tabs[0].value;
        }
    },
);

function filterOptions(
    options?: Record<string, string> | Array<{ value: string; label: string }>,
) {
    if (!options) return [] as Array<{ value: string; label: string }>;
    if (Array.isArray(options)) return options;
    return Object.entries(options).map(([value, label]) => ({
        value: String(value),
        label: String(label),
    }));
}

function reload(extra: Record<string, any> = {}) {
    loading.value = true;
    const searchFilters: Record<string, string> = {};
    Object.entries(filters.value).forEach(([key, value]) => {
        if (value) searchFilters[key] = value;
    });

    router.get(
        window.location.pathname,
        {
            q: search.value || undefined,
            tab: activeTab.value !== 'all' ? activeTab.value : undefined,
            search: Object.keys(searchFilters).length ? searchFilters : undefined,
            ...extra,
        },
        {
            preserveState: true,
            preserveScroll: true,
            only: props.reloadOnly ?? ['resource'],
            onFinish: () => {
                loading.value = false;
            },
        },
    );
}

function onSearch() {
    reload({ page: 1 });
}

function onTab(value: string | number) {
    activeTab.value = String(value);
    reload({ page: 1 });
}

function goPage(page: number) {
    if (page < 1 || page > (props.dataset.total_pages ?? 1)) return;
    reload({ page });
}

function rowId(row: Record<string, any>): string | number | null {
    const value = row?.[selectionColumn.value];
    return value === undefined || value === null ? null : value;
}

function isRowSelected(row: Record<string, any>) {
    const id = rowId(row);
    return id != null && selected.value.includes(id);
}

const allSelected = computed(
    () =>
        records.value.length > 0 &&
        records.value.every((row) => isRowSelected(row)),
);

function toggleAll(event: Event) {
    const checked = (event.target as HTMLInputElement).checked;
    if (checked) {
        selected.value = records.value
            .map((r) => rowId(r))
            .filter((id): id is string | number => id != null);
    } else {
        selected.value = [];
    }
}

function toggleOne(row: Record<string, any>, event: Event) {
    const id = rowId(row);
    if (id == null) return;
    const checked = (event.target as HTMLInputElement).checked;
    if (checked) {
        if (!selected.value.includes(id)) selected.value = [...selected.value, id];
    } else {
        selected.value = selected.value.filter((x) => x !== id);
    }
}

function clearSelection() {
    selected.value = [];
}

function cellValue(row: Record<string, any>, column: Column) {
    if (Object.prototype.hasOwnProperty.call(row, column.name)) {
        return row[column.name];
    }
    return column.name.split('.').reduce((acc: any, key: string) => acc?.[key], row);
}

function badgeVariant(column: Column, value: unknown) {
    const colors = column.colors ?? {};
    const key = Object.entries(colors).find(([, v]) => v === value)?.[0] ?? 'default';
    return toneVariant(key);
}

function toneVariant(tone?: string | null) {
    const map: Record<string, string> = {
        success: 'success',
        warning: 'warning',
        danger: 'danger',
        destructive: 'destructive',
        info: 'info',
        secondary: 'secondary',
        default: 'default',
        primary: 'default',
    };
    return map[tone || 'default'] ?? 'outline';
}

function tabTriggerClass(tab: { color?: string; badge_color?: string }) {
    const tone = tab.color || tab.badge_color || 'default';
    const map: Record<string, string> = {
        success:
            'border border-emerald-500/50 bg-transparent text-emerald-700 data-[state=active]:border-emerald-600 data-[state=active]:bg-emerald-600 data-[state=active]:text-white data-[state=active]:shadow-sm dark:text-emerald-400 dark:data-[state=active]:bg-emerald-600 dark:data-[state=active]:text-white',
        warning:
            'border border-amber-500/50 bg-transparent text-amber-700 data-[state=active]:border-amber-500 data-[state=active]:bg-amber-500 data-[state=active]:text-white data-[state=active]:shadow-sm dark:text-amber-400 dark:data-[state=active]:bg-amber-500 dark:data-[state=active]:text-white',
        danger:
            'border border-destructive/40 bg-transparent text-destructive data-[state=active]:border-destructive data-[state=active]:bg-destructive data-[state=active]:text-destructive-foreground data-[state=active]:shadow-sm',
        info:
            'border border-sky-500/50 bg-transparent text-sky-700 data-[state=active]:border-sky-600 data-[state=active]:bg-sky-600 data-[state=active]:text-white data-[state=active]:shadow-sm dark:text-sky-400 dark:data-[state=active]:bg-sky-600 dark:data-[state=active]:text-white',
        default:
            'border border-border bg-transparent text-foreground data-[state=active]:border-primary data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm',
    };
    return map[tone] ?? map.default;
}

function actionIcon(action: TableAction): Component | null {
    const name = (action.name || '').toLowerCase();
    if (name.includes('view') || name.includes('show')) return Eye;
    if (name.includes('edit') || name.includes('update')) return Pencil;
    if (name.includes('delete') || name.includes('destroy') || action.type === 'destructive') {
        return Trash2;
    }
    return null;
}

const activeFilterCount = computed(
    () => Object.values(filters.value).filter((v) => v !== '' && v != null).length,
);

function clearFilters() {
    filters.value = {};
    reload({ page: 1 });
}

function runAction(action: TableAction) {
    if (action.disabled) return;
    pendingBulk.value = null;
    if (action.requiresConfirmation) {
        pendingAction.value = action;
        confirmOpen.value = true;
        return;
    }
    executeAction(action);
}

function executeAction(action: TableAction) {
    if (action.url) {
        router.visit(action.url);
        return;
    }
    if (action.api) {
        router.visit(action.api, {
            method: (action.method?.toLowerCase() as any) || 'post',
            preserveScroll: true,
        });
    }
}

function runBulkAction(action: BulkAction) {
    if (!selected.value.length || !bulkUrl.value) return;
    pendingAction.value = null;
    if (action.requiresConfirmation) {
        pendingBulk.value = action;
        confirmOpen.value = true;
        return;
    }
    executeBulkAction(action);
}

function executeBulkAction(action: BulkAction) {
    if (!bulkUrl.value || !selected.value.length) return;
    loading.value = true;
    router.post(
        bulkUrl.value,
        {
            bulk_action: action.name,
            ids: selected.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
                selected.value = [];
            },
        },
    );
}

function confirmPending() {
    if (pendingBulk.value) {
        executeBulkAction(pendingBulk.value);
    } else if (pendingAction.value) {
        executeAction(pendingAction.value);
    }
    confirmOpen.value = false;
    pendingAction.value = null;
    pendingBulk.value = null;
}

const confirmTitle = computed(
    () =>
        pendingBulk.value?.confirmTitle ||
        pendingAction.value?.confirmTitle ||
        'Are you sure?',
);
const confirmText = computed(
    () =>
        pendingBulk.value?.confirmText ||
        pendingAction.value?.confirmText ||
        'This action cannot be undone.',
);
const confirmButton = computed(
    () =>
        pendingBulk.value?.confirmButton ||
        pendingAction.value?.confirmButton ||
        'Confirm',
);
const confirmDestructive = computed(
    () =>
        pendingBulk.value?.type === 'destructive' ||
        pendingAction.value?.type === 'destructive',
);
</script>

<template>
    <div class="space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <Tabs
                v-if="schema.tabs?.length"
                :model-value="activeTab"
                @update:model-value="onTab"
            >
                <TabsList class="h-auto gap-2 bg-transparent p-0">
                    <TabsTrigger
                        v-for="tab in schema.tabs"
                        :key="tab.value"
                        :value="tab.value"
                        :class="['rounded-full px-3 py-1.5 shadow-none', tabTriggerClass(tab)]"
                    >
                        {{ tab.label || tab.value }}
                    </TabsTrigger>
                </TabsList>
            </Tabs>

            <div class="flex flex-wrap items-center gap-2">
                <form
                    v-if="schema.search"
                    class="flex gap-2"
                    @submit.prevent="onSearch"
                >
                    <Input
                        v-model="search"
                        class="w-56"
                        :placeholder="schema.search.placeholder || 'Search...'"
                    />
                    <Button type="submit" variant="secondary" :disabled="loading">
                        Search
                    </Button>
                </form>

                <DropdownMenu v-if="selectable && bulkActions.length">
                    <DropdownMenuTrigger as-child>
                        <Button
                            type="button"
                            variant="outline"
                            class="gap-2"
                            :disabled="loading || !bulkUrl"
                        >
                            Bulk actions
                            <Badge
                                v-if="selected.length"
                                variant="secondary"
                                class="h-5 px-1.5"
                            >
                                {{ selected.length }}
                            </Badge>
                            <ChevronDown class="size-4 opacity-60" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-52 rounded-xl p-1.5">
                        <DropdownMenuLabel class="px-2 py-1.5 text-xs font-normal text-muted-foreground">
                            <template v-if="selected.length">
                                {{ selected.length }} selected
                            </template>
                            <template v-else>Select rows first</template>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            v-for="action in bulkActions"
                            :key="action.name"
                            class="cursor-pointer gap-2 rounded-lg"
                            :disabled="!selected.length || loading || !bulkUrl"
                            :class="{
                                'text-destructive focus:bg-destructive/10 focus:text-destructive':
                                    action.type === 'destructive',
                            }"
                            @click="runBulkAction(action)"
                        >
                            {{ action.label }}
                        </DropdownMenuItem>
                        <DropdownMenuSeparator v-if="selected.length" />
                        <DropdownMenuItem
                            v-if="selected.length"
                            class="cursor-pointer rounded-lg"
                            @click="clearSelection"
                        >
                            Clear selection
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Popover v-if="schema.filters?.length">
                    <PopoverTrigger as-child>
                        <Button type="button" variant="outline" class="gap-2">
                            <Filter class="size-4" />
                            Filters
                            <Badge
                                v-if="activeFilterCount"
                                variant="secondary"
                                class="h-5 px-1.5"
                            >
                                {{ activeFilterCount }}
                            </Badge>
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-80 space-y-4" align="end">
                        <div class="space-y-1">
                            <p class="text-sm font-medium">Filters</p>
                            <p class="text-xs text-muted-foreground">
                                Narrow the table results.
                            </p>
                        </div>

                        <div
                            v-for="filter in schema.filters || []"
                            :key="filter.name"
                            class="space-y-2"
                        >
                            <p class="text-xs font-medium text-muted-foreground">
                                {{ filter.label || filter.name }}
                            </p>
                            <Select
                                v-if="filter.type === 'select' || filter.options"
                                :model-value="filters[filter.name] || '__all'"
                                @update:model-value="
                                    (v) => {
                                        const value = String(v ?? '');
                                        filters[filter.name] =
                                            value === '__all' ? '' : value;
                                    }
                                "
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="filter.label || filter.name" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__all">All</SelectItem>
                                    <SelectItem
                                        v-for="opt in filterOptions(filter.options)"
                                        :key="opt.value"
                                        :value="opt.value"
                                    >
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex justify-between gap-2">
                            <Button type="button" variant="ghost" size="sm" @click="clearFilters">
                                Clear
                            </Button>
                            <Button type="button" size="sm" @click="reload({ page: 1 })">
                                Apply
                            </Button>
                        </div>
                    </PopoverContent>
                </Popover>
            </div>
        </div>

        <div class="rounded-2xl border bg-background/60">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead v-if="selectable" class="w-12 px-3">
                            <input
                                type="checkbox"
                                class="size-4 cursor-pointer rounded-[4px] border border-input accent-primary"
                                :checked="allSelected"
                                aria-label="Select all"
                                @change="toggleAll"
                                @click.stop
                            />
                        </TableHead>
                        <TableHead v-for="column in columns" :key="column.name">
                            {{ column.label || column.name }}
                        </TableHead>
                        <TableHead class="w-12" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="!records.length">
                        <TableCell
                            :colspan="columns.length + (selectable ? 2 : 1)"
                            class="h-24 text-center text-muted-foreground"
                        >
                            No results.
                        </TableCell>
                    </TableRow>
                    <TableRow
                        v-for="(row, rowIndex) in records"
                        :key="rowId(row) ?? rowIndex"
                        :class="cn(isRowSelected(row) && 'bg-muted/40')"
                    >
                        <TableCell v-if="selectable" class="w-12 px-3">
                            <input
                                type="checkbox"
                                class="size-4 cursor-pointer rounded-[4px] border border-input accent-primary"
                                :checked="isRowSelected(row)"
                                :aria-label="`Select row`"
                                @change="(e) => toggleOne(row, e)"
                                @click.stop
                            />
                        </TableCell>
                        <TableCell v-for="column in columns" :key="column.name">
                            <Badge
                                v-if="column.type === 'badge'"
                                :variant="badgeVariant(column, cellValue(row, column)) as any"
                            >
                                {{ cellValue(row, column) }}
                            </Badge>
                            <span
                                v-else-if="column.type === 'boolean'"
                                class="text-muted-foreground"
                            >
                                {{ cellValue(row, column) ? 'Yes' : 'No' }}
                            </span>
                            <img
                                v-else-if="column.type === 'image' && cellValue(row, column)"
                                :src="String(cellValue(row, column))"
                                alt=""
                                class="h-8 w-8 rounded object-cover"
                            />
                            <span
                                v-else-if="column.type === 'json'"
                                class="font-mono text-xs text-muted-foreground"
                            >
                                {{ cellValue(row, column) ?? '—' }}
                            </span>
                            <span v-else>{{ cellValue(row, column) }}</span>
                        </TableCell>
                        <TableCell class="w-12">
                            <DropdownMenu v-if="row.table_actions?.length">
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        class="rounded-lg text-muted-foreground hover:text-foreground"
                                    >
                                        <MoreHorizontal class="size-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    :side-offset="6"
                                    class="w-44 rounded-xl border bg-popover p-1.5 shadow-lg"
                                >
                                    <template
                                        v-for="(action, actionIndex) in row.table_actions"
                                        :key="action.name || actionIndex"
                                    >
                                        <DropdownMenuSeparator
                                            v-if="
                                                actionIndex > 0 &&
                                                action.type === 'destructive'
                                            "
                                            class="my-1"
                                        />
                                        <DropdownMenuItem
                                            :disabled="action.disabled"
                                            class="cursor-pointer gap-2 rounded-lg px-2.5 py-2"
                                            :class="{
                                                'text-destructive focus:bg-destructive/10 focus:text-destructive':
                                                    action.type === 'destructive',
                                            }"
                                            @click="runAction(action)"
                                        >
                                            <component
                                                :is="actionIcon(action)"
                                                v-if="actionIcon(action)"
                                                class="size-4 opacity-70"
                                            />
                                            <span>{{ action.label }}</span>
                                        </DropdownMenuItem>
                                    </template>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div
            v-if="(dataset.total_pages ?? 1) > 1"
            class="flex items-center justify-between text-sm text-muted-foreground"
        >
            <span>{{ dataset.total_records ?? 0 }} total</span>
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="(dataset.current_page ?? 1) <= 1 || loading"
                    @click="goPage((dataset.current_page ?? 1) - 1)"
                >
                    Previous
                </Button>
                <span>
                    Page {{ dataset.current_page }} / {{ dataset.total_pages }}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="
                        (dataset.current_page ?? 1) >= (dataset.total_pages ?? 1) || loading
                    "
                    @click="goPage((dataset.current_page ?? 1) + 1)"
                >
                    Next
                </Button>
            </div>
        </div>

        <AlertDialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ confirmTitle }}</AlertDialogTitle>
                    <AlertDialogDescription>{{ confirmText }}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel
                        @click="
                            pendingAction = null;
                            pendingBulk = null;
                        "
                    >
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        :class="confirmDestructive ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90' : undefined"
                        @click="confirmPending"
                    >
                        {{ confirmButton }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
