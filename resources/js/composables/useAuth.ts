import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuth() {
    const page = usePage();

    // Assumes Inertia shares 'auth.user'
    const user = computed(() => page.props.auth?.user || null);

    const logout = async () => {
        router.post('/logout');
    };

    return {
        user,
        logout,
    };
}
