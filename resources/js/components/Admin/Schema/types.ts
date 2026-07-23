import { inject, provide, type InjectionKey } from 'vue';
import { useForm } from '@inertiajs/vue3';

export type SchemaNode = {
    type: string;
    name?: string | null;
    label?: string;
    helpText?: string;
    description?: string;
    placeholder?: string;
    required?: boolean;
    options?: Record<string, string> | Array<{ label: string; value: string }>;
    schema?: SchemaNode[];
    action?: string;
    method?: string;
    columns?: number;
    level?: number;
    variant?: string;
    justify?: string;
    direction?: string;
    url?: string;
    type_attr?: string;
    buttonType?: string;
    is_back?: boolean;
    column_span?: number | null;
    width?: string | null;
    content?: string;
    visibleWhen?: { field: string; value: unknown; operator?: string };
    disabledWhen?: { field: string; value: unknown; operator?: string };
    props?: Record<string, unknown>;
    [key: string]: unknown;
};

export type InertiaForm = ReturnType<typeof useForm<Record<string, unknown>>>;

export const sduiFormKey: InjectionKey<InertiaForm | Record<string, unknown> | null> = Symbol('sduiForm');

export function useSduiForm() {
    return inject(sduiFormKey, null);
}

export function provideSduiForm(form: InertiaForm | Record<string, unknown>) {
    provide(sduiFormKey, form);
}

export function collectDefaults(items: SchemaNode[], bag: Record<string, unknown> = {}) {
    for (const item of items) {
        if (item.name && !(item.name in bag)) {
            if (item.type === 'multi-select' || item.type === 'list-input') bag[item.name] = [];
            else if (item.type === 'checkbox' || item.type === 'toggle') bag[item.name] = false;
            else if (item.type === 'json-input') bag[item.name] = [];
            else if (item.type === 'json-code') bag[item.name] = item.placeholder || '{}';
            else if (item.type === 'number-input') bag[item.name] = item.default ?? 0;
            else if (item.type === 'file-input') bag[item.name] = '';
            else bag[item.name] = item.default ?? '';
        }
        if (item.schema) collectDefaults(item.schema, bag);
    }
    return bag;
}

type SchemaCondition = { field: string; value: unknown; operator?: string };

/** Returns whether a condition matches. No condition → false (does not match). */
export function evaluate(
    condition: SchemaCondition | undefined,
    data: Record<string, unknown>,
): boolean {
    if (!condition?.field) return false;

    const left = data[condition.field];
    const right = condition.value;
    const op = condition.operator ?? '=';

    switch (op) {
        case '!=':
            return left !== right;
        case '>':
            return Number(left) > Number(right);
        case '<':
            return Number(left) < Number(right);
        case '>=':
            return Number(left) >= Number(right);
        case '<=':
            return Number(left) <= Number(right);
        case 'in':
            return Array.isArray(right) && right.includes(left);
        case 'not_in':
            return Array.isArray(right) && !right.includes(left);
        default:
            return left === right;
    }
}

function conditionOf(
    node: SchemaNode,
    key: 'visibleWhen' | 'disabledWhen',
): SchemaCondition | undefined {
    return (node[key] ?? node.props?.[key]) as SchemaCondition | undefined;
}

/** Visible unless a visibleWhen condition exists and fails. */
export function isNodeVisible(node: SchemaNode, data: Record<string, unknown>): boolean {
    const condition = conditionOf(node, 'visibleWhen');
    if (!condition?.field) return true;
    return evaluate(condition, data);
}

/** Disabled only when a disabledWhen condition exists and matches. */
export function isNodeDisabled(node: SchemaNode, data: Record<string, unknown>): boolean {
    const condition = conditionOf(node, 'disabledWhen');
    if (!condition?.field) return false;
    return evaluate(condition, data);
}

export function optionEntries(options: SchemaNode['options']) {
    if (!options) return [] as Array<{ label: string; value: string }>;
    if (Array.isArray(options)) {
        return options.map((o) =>
            typeof o === 'string'
                ? { label: o, value: o }
                : { label: String(o.label), value: String(o.value) },
        );
    }
    return Object.entries(options).map(([value, label]) => ({
        value: String(value),
        label: String(label),
    }));
}

export function buttonVariant(variant?: string) {
    const map: Record<string, string> = {
        primary: 'default',
        default: 'default',
        secondary: 'secondary',
        outline: 'outline',
        ghost: 'ghost',
        link: 'link',
        danger: 'destructive',
        destructive: 'destructive',
    };
    return map[variant || 'default'] ?? 'default';
}

export type SchemaNodeProps = {
    node: SchemaNode;
    form: any;
    initialData?: Record<string, unknown>;
};
