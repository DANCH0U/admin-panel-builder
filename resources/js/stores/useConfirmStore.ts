import { defineStore } from 'pinia';
import { ref } from 'vue';

interface ConfirmOptions {
    title?: string;
    description?: string;
    label?: string;
    variant?: 'primary' | 'secondary' | 'ghost' | 'border' | 'plain';
    color?: 'primary' | 'danger' | 'info' | 'success' | 'warning';
    icon?: string;
}

export const useConfirmStore = defineStore('confirm', () => {
    const isOpen = ref(false);
    const options = ref<ConfirmOptions>({});
    const resolvePromise = ref<((value: boolean) => void) | null>(null);

    const confirm = (
        opts: ConfirmOptions | string,
        description?: string,
        label?: string,
        variant?: any,
    ) => {
        if (typeof opts === 'string') {
            options.value = {
                title: opts,
                description: description,
                label: label,
                variant: variant === 'danger' ? 'primary' : variant, // Map legacy variant if needed
                color: variant === 'danger' ? 'danger' : 'primary',
            };
        } else {
            options.value = opts;
        }

        isOpen.value = true;

        return new Promise<boolean>((resolve) => {
            resolvePromise.value = resolve;
        });
    };

    const submit = () => {
        if (resolvePromise.value) {
            resolvePromise.value(true);
        }
        isOpen.value = false;
        options.value = {};
    };

    const cancel = () => {
        if (resolvePromise.value) {
            resolvePromise.value(false);
        }
        isOpen.value = false;
        options.value = {};
    };

    return {
        isOpen,
        options,
        confirm,
        submit,
        cancel,
    };
});
