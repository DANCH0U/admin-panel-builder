import type { Component } from 'vue';
import SchemaCard from './nodes/SchemaCard.vue';
import SchemaCheckbox from './nodes/SchemaCheckbox.vue';
import SchemaDateTime from './nodes/SchemaDateTime.vue';
import SchemaFileInput from './nodes/SchemaFileInput.vue';
import SchemaFlex from './nodes/SchemaFlex.vue';
import SchemaForm from './nodes/SchemaForm.vue';
import SchemaGrid from './nodes/SchemaGrid.vue';
import SchemaHeading from './nodes/SchemaHeading.vue';
import SchemaImage from './nodes/SchemaImage.vue';
import SchemaJsonCode from './nodes/SchemaJsonCode.vue';
import SchemaJsonInput from './nodes/SchemaJsonInput.vue';
import SchemaKeyValue from './nodes/SchemaKeyValue.vue';
import SchemaListInput from './nodes/SchemaListInput.vue';
import SchemaMultiSelect from './nodes/SchemaMultiSelect.vue';
import SchemaNumber from './nodes/SchemaNumber.vue';
import SchemaSection from './nodes/SchemaSection.vue';
import SchemaSelect from './nodes/SchemaSelect.vue';
import SchemaTabs from './nodes/SchemaTabs.vue';
import SchemaText from './nodes/SchemaText.vue';
import SchemaTextarea from './nodes/SchemaTextarea.vue';
import SchemaTextInput from './nodes/SchemaTextInput.vue';
import SchemaToggle from './nodes/SchemaToggle.vue';
import SchemaUiButton from './nodes/SchemaUiButton.vue';
import SchemaUnsupported from './nodes/SchemaUnsupported.vue';

/**
 * Maps PHP Schema `type` strings to Vue renderers (shadcn/vue).
 * To add a component: create a node under ./nodes and register it here.
 */
const registry: Record<string, Component> = {
    form: SchemaForm,
    grid: SchemaGrid,
    flex: SchemaFlex,
    section: SchemaSection,
    card: SchemaCard,
    tabs: SchemaTabs,
    'ui-heading': SchemaHeading,
    'ui-text': SchemaText,
    'ui-image': SchemaImage,
    'ui-button': SchemaUiButton,
    'key-value': SchemaKeyValue,
    'text-input': SchemaTextInput,
    textarea: SchemaTextarea,
    'number-input': SchemaNumber,
    'select-input': SchemaSelect,
    'multi-select': SchemaMultiSelect,
    'list-input': SchemaListInput,
    'json-input': SchemaJsonInput,
    'json-code': SchemaJsonCode,
    'file-input': SchemaFileInput,
    toggle: SchemaToggle,
    checkbox: SchemaCheckbox,
    'datetime-input': SchemaDateTime,
};

export function registerSchemaComponent(type: string, component: Component): void {
    registry[type] = component;
}

export function resolveSchemaComponent(type: string): Component {
    return registry[type] ?? SchemaUnsupported;
}

export function schemaComponentTypes(): string[] {
    return Object.keys(registry);
}
