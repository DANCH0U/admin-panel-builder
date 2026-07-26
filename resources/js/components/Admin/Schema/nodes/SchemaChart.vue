<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Loader2 } from 'lucide-vue-next';
import type { SchemaNodeProps } from '../types';
import { cn } from '@/lib/utils';
import { useApi } from '@/composables/useApi';

const props = defineProps<SchemaNodeProps>();
const { get } = useApi();

type ChartSeries = number[] | Array<{ name?: string; data?: number[] } & Record<string, unknown>>;

type ChartPayload = {
    series?: ChartSeries;
    categories?: string[];
    labels?: string[];
    colors?: string[];
    options?: Record<string, unknown>;
};

function cssVar(name: string, fallback: string): string {
    if (typeof window === 'undefined') return fallback;
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
}

const apiUrl = computed(() => {
    const value = props.node.api;
    return typeof value === 'string' && value.trim() !== '' ? value.trim() : null;
});

const loading = ref(Boolean(apiUrl.value));
const loadError = ref<string | null>(null);
const liveSeries = ref<ChartSeries | null>(null);
const liveCategories = ref<string[] | null>(null);
const liveLabels = ref<string[] | null>(null);
const liveColors = ref<string[] | null>(null);
const liveOptions = ref<Record<string, unknown> | null>(null);

const chartType = computed(() => String(props.node.chartType || 'area'));
const height = computed(() => Number(props.node.height || 320));
const series = computed(() => liveSeries.value ?? (props.node.series as ChartSeries) ?? []);
const categories = computed(() => liveCategories.value ?? (props.node.categories as string[]) ?? []);
const labels = computed(() => liveLabels.value ?? (props.node.labels as string[]) ?? []);
const colors = computed(() => {
    const custom = liveColors.value ?? (props.node.colors as string[] | undefined);
    if (custom?.length) return custom;
    // ApexCharts needs hex/rgb — theme oklch tokens are not always accepted.
    return ['#3b5bdb', '#12b886', '#fab005', '#be4bdb', '#fd7e14'];
});

const isPieLike = computed(() => ['pie', 'donut', 'radialBar'].includes(chartType.value));

const options = computed(() => {
    const foreground = cssVar('--foreground', '#1f2937');
    const muted = cssVar('--muted-foreground', '#6b7280');
    const border = cssVar('--border', '#e5e7eb');
    const sparkline = Boolean(props.node.sparkline);
    const toolbar = Boolean(props.node.toolbar);

    const base: Record<string, unknown> = {
        chart: {
            type: chartType.value,
            toolbar: { show: toolbar },
            fontFamily: 'inherit',
            background: 'transparent',
            sparkline: { enabled: sparkline },
            animations: { enabled: true, speed: 500 },
        },
        colors: colors.value,
        dataLabels: { enabled: isPieLike.value && !sparkline },
        stroke: {
            curve: 'smooth',
            width: isPieLike.value ? 0 : 2,
        },
        fill: {
            type: chartType.value === 'area' ? 'gradient' : 'solid',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 90, 100],
            },
        },
        grid: {
            borderColor: border,
            strokeDashArray: 4,
            padding: { left: 8, right: 8 },
            show: !sparkline && !isPieLike.value,
        },
        legend: {
            show: !sparkline,
            position: 'bottom',
            labels: { colors: muted },
        },
        tooltip: {
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
        },
        xaxis: isPieLike.value
            ? undefined
            : {
                  categories: categories.value,
                  labels: { style: { colors: muted } },
                  axisBorder: { color: border },
                  axisTicks: { color: border },
              },
        yaxis: isPieLike.value
            ? undefined
            : {
                  labels: { style: { colors: muted } },
              },
        labels: isPieLike.value ? labels.value : undefined,
        plotOptions: isPieLike.value
            ? {
                  pie: {
                      donut: {
                          size: chartType.value === 'donut' ? '65%' : '0%',
                          labels: {
                              show: chartType.value === 'donut',
                              name: { color: foreground },
                              value: { color: foreground },
                              total: { show: chartType.value === 'donut', color: muted },
                          },
                      },
                  },
              }
            : undefined,
    };

    const nodeOptions = (props.node.options as Record<string, unknown>) ?? {};
    const merged = deepMerge(base, nodeOptions);
    return liveOptions.value ? deepMerge(merged, liveOptions.value) : merged;
});

function deepMerge(
    target: Record<string, unknown>,
    source: Record<string, unknown>,
): Record<string, unknown> {
    const out: Record<string, unknown> = { ...target };
    for (const [key, value] of Object.entries(source)) {
        if (
            value &&
            typeof value === 'object' &&
            !Array.isArray(value) &&
            typeof out[key] === 'object' &&
            out[key] !== null &&
            !Array.isArray(out[key])
        ) {
            out[key] = deepMerge(
                out[key] as Record<string, unknown>,
                value as Record<string, unknown>,
            );
        } else {
            out[key] = value;
        }
    }
    return out;
}

function unwrapPayload(raw: unknown): ChartPayload | null {
    if (!raw || typeof raw !== 'object') return null;
    const obj = raw as Record<string, unknown>;
    if ('series' in obj) return obj as ChartPayload;
    if (obj.data && typeof obj.data === 'object' && 'series' in (obj.data as object)) {
        return obj.data as ChartPayload;
    }
    return null;
}

function applyPayload(raw: unknown): boolean {
    const payload = unwrapPayload(raw);
    if (!payload?.series) return false;

    liveSeries.value = payload.series;
    if (Array.isArray(payload.categories)) liveCategories.value = payload.categories;
    if (Array.isArray(payload.labels)) liveLabels.value = payload.labels;
    if (Array.isArray(payload.colors)) liveColors.value = payload.colors;
    if (payload.options && typeof payload.options === 'object') {
        liveOptions.value = payload.options as Record<string, unknown>;
    }
    return true;
}

async function loadFromApi(url: string) {
    loading.value = true;
    loadError.value = null;

    const { data, error } = await get(url);

    if (error) {
        loadError.value = 'Failed to load chart data.';
        loading.value = false;
        return;
    }

    if (!applyPayload(data)) {
        loadError.value = 'Invalid chart response.';
        loading.value = false;
        return;
    }

    loading.value = false;
}

onMounted(() => {
    if (apiUrl.value) {
        void loadFromApi(apiUrl.value);
    }
});

watch(apiUrl, (url) => {
    if (url) {
        void loadFromApi(url);
    }
});
</script>

<template>
    <div
        :class="
            cn(
                'space-y-3',
                node.bordered && 'admin-surface p-4 md:p-5',
                node.column_span ? `col-span-${node.column_span}` : null,
            )
        "
    >
        <div v-if="node.label || node.helpText" class="space-y-1">
            <h3 v-if="node.label" class="text-base font-semibold leading-snug tracking-tight">
                {{ node.label }}
            </h3>
            <p v-if="node.helpText" class="text-sm leading-relaxed text-muted-foreground">
                {{ node.helpText }}
            </p>
        </div>

        <div class="relative w-full" :style="{ minHeight: `${height}px` }">
            <div
                v-if="loading"
                class="absolute inset-0 z-10 flex items-center justify-center rounded-md bg-background/70"
            >
                <Loader2 class="size-6 animate-spin text-primary" aria-label="Loading chart" />
            </div>

            <div
                v-else-if="loadError"
                class="flex items-center justify-center text-sm text-muted-foreground"
                :style="{ minHeight: `${height}px` }"
            >
                {{ loadError }}
            </div>

            <apexchart
                v-else
                :type="chartType"
                :height="height"
                :options="options"
                :series="series"
                width="100%"
            />
        </div>
    </div>
</template>
