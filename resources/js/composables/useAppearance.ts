import { ref } from 'vue';

const preference = ref('light');

export function initializeTheme() {
    if (
        localStorage.getItem('theme') === 'dark' ||
        (!('theme' in localStorage) &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)
    ) {
        document.documentElement.classList.add('dark');
        preference.value = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        preference.value = 'light';
    }
}

export function useColorMode() {
    return {
        get preference() {
            return preference.value;
        },
        set preference(value) {
            preference.value = value;
            if (value === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        },
    };
}
