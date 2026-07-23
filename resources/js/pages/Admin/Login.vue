<script setup>
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/composables/useI18n';
import LoginLayout from '@/layouts/LoginLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({ layout: LoginLayout });

const page = usePage();
const { tc } = useI18n();
const remember = ref(true);

const form = useForm({
    email: '',
    password: '',
    remember: 1,
});

const appName = computed(() => page.props.panel?.name || 'Admin Panel');

const submit = () => {
    form.remember = remember.value ? 1 : 0;
    form.clearErrors();
    form.post('/login');
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-background px-4">
        <div class="w-full max-w-sm space-y-6">
            <div class="space-y-1 text-center">
                <p class="text-sm text-muted-foreground">{{ appName }}</p>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ tc('login_header') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ tc('login_description') }}
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="email">{{ tc('email') }}</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                    />
                    <p v-if="form.errors.email" class="text-sm text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="password">{{ tc('password') }}</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="remember"
                        :checked="remember"
                        @update:checked="remember = Boolean($event)"
                    />
                    <Label for="remember">{{ tc('remember_me') }}</Label>
                </div>

                <Button class="w-full" type="submit" :disabled="form.processing">
                    {{ tc('login') }}
                </Button>
            </form>
        </div>
    </div>
</template>
