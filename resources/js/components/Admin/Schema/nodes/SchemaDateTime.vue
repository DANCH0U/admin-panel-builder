<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { isNodeDisabled, type SchemaNodeProps } from '../types';
import { CalendarDate, type DateValue, parseDate } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import SchemaFieldError from './SchemaFieldError.vue';

const props = defineProps<SchemaNodeProps>();
const open = ref(false);

function disabled() {
    return isNodeDisabled(props.node, props.form) || Boolean(props.node.disabled);
}

function splitValue(raw: unknown): { date: string; time: string } {
    const value = String(raw ?? '');
    if (!value) return { date: '', time: '00:00' };
    const [date, time = '00:00'] = value.includes('T') ? value.split('T') : value.split(' ');
    return { date: date || '', time: (time || '00:00').slice(0, 5) };
}

const calendarValue = computed<DateValue | undefined>(() => {
    const { date } = splitValue(props.form?.[props.node.name!]);
    if (!date) return undefined;
    try {
        return parseDate(date);
    } catch {
        return undefined;
    }
});

const display = computed(() => {
    const { date, time } = splitValue(props.form?.[props.node.name!]);
    if (!date) return props.node.placeholder || 'Pick a date';
    return props.node.withTime === false ? date : `${date} ${time}`;
});

function write(date: string, time: string) {
    if (!props.node.name) return;
    if (!date) {
        props.form[props.node.name] = '';
        return;
    }
    props.form[props.node.name] =
        props.node.withTime === false ? date : `${date}T${time || '00:00'}`;
}

function onCalendar(value: DateValue | undefined) {
    if (!value) return;
    const date = value.toString();
    const { time } = splitValue(props.form?.[props.node.name!]);
    write(date, time);
    if (props.node.withTime === false) open.value = false;
}

function onTime(time: string) {
    const { date } = splitValue(props.form?.[props.node.name!]);
    write(date || new CalendarDate(
        new Date().getFullYear(),
        new Date().getMonth() + 1,
        new Date().getDate(),
    ).toString(), time);
}
</script>

<template>
    <div class="space-y-2">
        <Label v-if="node.name">
            {{ node.label || node.name }}
            <span v-if="node.required" class="text-destructive">*</span>
        </Label>
        <Popover v-model:open="open">
            <PopoverTrigger as-child>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="disabled()"
                    :class="
                        cn(
                            'w-full justify-start text-left font-normal',
                            !form[node.name!] && 'text-muted-foreground',
                        )
                    "
                >
                    <CalendarIcon class="mr-2 size-4" />
                    {{ display }}
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0" align="start">
                <Calendar
                    :model-value="calendarValue"
                    initial-focus
                    @update:model-value="onCalendar"
                />
                <div v-if="node.withTime !== false" class="border-t p-3">
                    <Label class="mb-2 block text-xs">Time</Label>
                    <Input
                        type="time"
                        :model-value="splitValue(form[node.name!]).time"
                        @update:model-value="onTime(String($event))"
                    />
                </div>
            </PopoverContent>
        </Popover>
        <p v-if="node.hint || node.helpText" class="text-xs text-muted-foreground">
            {{ node.hint || node.helpText }}
        </p>
        <SchemaFieldError :form="form" :name="node.name" />
    </div>
</template>
