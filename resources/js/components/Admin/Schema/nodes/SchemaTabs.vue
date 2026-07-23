<script setup lang="ts">
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { SchemaNodeProps } from '../types';
import SchemaRenderer from '../SchemaRenderer.vue';

defineProps<SchemaNodeProps>();
</script>

<template>
    <Tabs :default-value="node.schema?.[0]?.name || '0'" class="w-full">
        <TabsList>
            <TabsTrigger
                v-for="(tab, tabIndex) in node.schema || []"
                :key="tabIndex"
                :value="tab.name || String(tabIndex)"
            >
                {{ tab.label || tab.name }}
            </TabsTrigger>
        </TabsList>
        <TabsContent
            v-for="(tab, tabIndex) in node.schema || []"
            :key="`content-${tabIndex}`"
            :value="tab.name || String(tabIndex)"
            class="mt-4"
        >
            <SchemaRenderer
                v-if="tab.schema?.length"
                :schema="tab.schema"
                :form="form"
                :initial-data="initialData"
            />
        </TabsContent>
    </Tabs>
</template>
