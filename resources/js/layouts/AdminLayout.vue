<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import NotificationHost from '@/components/Admin/NotificationHost.vue';
import AdminLoadingIndicator from '@/components/Admin/AdminLoadingIndicator.vue';
import { useAdminConfig } from '@/composables/useAdminConfig';
import { useAuth } from '@/composables/useAuth';
import { useI18n } from '@/composables/useI18n';
import { useShellData } from '@/composables/useShellData';
import { useAdminStore } from '@/stores/useAdminStore';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Check,
    ChevronsUpDown,
    Languages,
    LayoutGrid,
    LogOut,
    Menu,
    Moon,
    Sun,
    UserRound,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const page = usePage();
const { menu } = useShellData();
const admin = useAdminStore();
const { logout, user } = useAuth();
const { ta } = useI18n();
const { adminPath, name: panelName, logoUrl } = useAdminConfig();

const dark = ref(false);

type AdminLanguage = {
    label: string;
    locale: string;
    font: string;
    family?: string;
};

type AdminPanelLink = {
    key: string;
    name: string;
    path: string;
    current?: boolean;
};

const panelProps = computed(() => (page.props.panel as any) ?? {});
const locale = computed(() => String(panelProps.value.locale || 'en'));
const languages = computed(
    (): AdminLanguage[] => (panelProps.value.languages as AdminLanguage[]) || [],
);
const panels = computed(
    (): AdminPanelLink[] => (panelProps.value.panels as AdminPanelLink[]) || [],
);

function fontFamilyName(lang?: AdminLanguage | null): string {
    if (lang?.family) return lang.family;
    const match = lang?.font?.match(/family=([^:&]+)/);
    if (!match?.[1]) return 'Plus Jakarta Sans';
    return decodeURIComponent(match[1].replace(/\+/g, ' '));
}

function applyLanguageFont(lang?: AdminLanguage | null) {
    if (!lang?.font || typeof document === 'undefined') return;

    const family = fontFamilyName(lang);
    document.documentElement.style.setProperty('--admin-font-family', `'${family}'`);

    // Recreate the <link> so browsers always reload the stylesheet on locale change
    document.getElementById('admin-lang-font')?.remove();
    const link = document.createElement('link');
    link.id = 'admin-lang-font';
    link.rel = 'stylesheet';
    link.href = lang.font;
    link.onload = () => {
        document.documentElement.style.setProperty('--admin-font-family', `'${family}'`);
    };
    document.head.appendChild(link);
}

onMounted(() => {
    dark.value = document.documentElement.classList.contains('dark');
    const stored = localStorage.getItem('theme');
    if (stored === 'dark') dark.value = true;
    applyLanguageFont(languages.value.find((l) => l.locale === locale.value) ?? languages.value[0]);
});

watch(dark, (value) => {
    document.documentElement.classList.toggle('dark', value);
    localStorage.setItem('theme', value ? 'dark' : 'light');
});

watch(
    [locale, languages],
    () => {
        applyLanguageFont(languages.value.find((l) => l.locale === locale.value) ?? languages.value[0]);
    },
    { deep: true },
);

const appName = computed(() => panelName.value);

const currentPath = computed(() => normalizePath(page.url.split('?')[0] || '/'));

/** Link items only (skip section labels). */
const menuLinks = computed(() =>
    (menu.value as any[]).filter((item) => item?.type !== 'label' && (item?.url || item?.href)),
);

function normalizePath(path: string): string {
    const trimmed = path.trim();
    if (!trimmed || trimmed === '/') return '/';
    return trimmed.replace(/\/+$/, '') || '/';
}

/**
 * Active when this item is the best (longest) match for the current URL.
 * Avoids `/admin` highlighting on every `/admin/posts/...` page.
 */
function isActive(url?: string): boolean {
    if (!url) return false;
    const target = normalizePath(url);
    const current = currentPath.value;

    const matches = menuLinks.value
        .map((item) => normalizePath(String(item.url || item.href)))
        .filter((href) => current === href || current.startsWith(`${href}/`))
        .sort((a, b) => b.length - a.length);

    return matches[0] === target;
}

function menuLabel(item: any) {
    const key = item.key || item.title || item.label || item.name;
    if (!key) return 'Item';
    const translated = ta(String(key));
    return translated !== String(key) ? translated : String(item.title || item.label || key);
}

function toggleTheme() {
    dark.value = !dark.value;
}

function setLocale(code: string) {
    if (code === locale.value) return;
    router.get(
        `/locale/${code}`,
        { return: page.url },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <NotificationHost />
        <AdminLoadingIndicator />

        <div class="flex min-h-screen">
            <div
                v-if="admin.sideBarOpen"
                class="fixed inset-0 z-40 bg-black/40 lg:hidden"
                @click="admin.sideBarOpen = false"
            />

            <aside
                :class="
                    cn(
                        'fixed inset-y-0 start-0 z-50 flex h-screen w-64 flex-col border-e border-sidebar-border bg-sidebar text-sidebar-foreground shadow-sm transition-transform',
                        admin.sideBarOpen
                            ? 'translate-x-0'
                            : '-translate-x-full lg:translate-x-0',
                    )
                "
            >
                <div class="flex h-16 shrink-0 items-center gap-3 px-4">
                    <Link :href="adminPath()" class="flex min-w-0 items-center gap-3">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            alt=""
                            class="h-9 w-9 shrink-0 rounded-xl object-contain ring-1 ring-border"
                        />
                        <span
                            v-else
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sidebar-primary text-sm font-bold text-sidebar-primary-foreground"
                        >
                            {{ appName.charAt(0).toUpperCase() }}
                        </span>
                        <span class="truncate text-[15px] font-semibold tracking-tight">{{
                            appName
                        }}</span>
                    </Link>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="ms-auto lg:hidden"
                        @click="admin.sideBarOpen = false"
                    >
                        <X class="size-4" />
                    </Button>
                </div>

                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 pb-3">
                    <template v-for="(item, index) in menu" :key="index">
                        <div
                            v-if="item.type === 'label'"
                            class="px-2 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground"
                        >
                            {{ menuLabel(item) }}
                        </div>
                        <Link
                            v-else
                            :href="item.url || item.href || '#'"
                            :class="
                                cn(
                                    'flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm transition-all',
                                    isActive(item.url || item.href)
                                        ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground'
                                        : 'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                                )
                            "
                            @click="admin.sideBarOpen = false"
                        >
                            <Icon
                                v-if="item.icon"
                                :icon="item.icon"
                                class="size-5 shrink-0 opacity-70"
                            />
                            <span class="truncate">{{ menuLabel(item) }}</span>
                        </Link>
                    </template>
                </nav>

                <div class="mt-auto shrink-0 border-t border-sidebar-border p-3">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="flex w-full items-center gap-2.5 rounded-xl bg-sidebar-accent p-2.5 text-left transition-colors hover:bg-sidebar-accent/80"
                            >
                                <Avatar class="size-9 ring-1 ring-border">
                                    <AvatarFallback>
                                        {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium">{{ user?.name }}</p>
                                    <p class="truncate text-xs text-muted-foreground">
                                        {{ user?.email }}
                                    </p>
                                </div>
                                <ChevronsUpDown class="size-4 shrink-0 text-muted-foreground" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            side="top"
                            align="start"
                            :side-offset="8"
                            class="w-56 rounded-xl p-1.5 shadow-lg"
                        >
                            <DropdownMenuLabel class="px-2 py-1.5 font-normal">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm font-medium">{{ user?.name }}</span>
                                    <span class="text-xs text-muted-foreground">{{
                                        user?.email
                                    }}</span>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child class="cursor-pointer gap-2 rounded-lg">
                                <Link :href="adminPath('profile')">
                                    <UserRound class="size-4" />
                                    Profile
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                class="cursor-pointer gap-2 rounded-lg"
                                @click="toggleTheme"
                            >
                                <Sun v-if="dark" class="size-4" />
                                <Moon v-else class="size-4" />
                                {{ dark ? 'Light' : 'Dark' }}
                            </DropdownMenuItem>
                            <template v-if="panels.length">
                                <DropdownMenuSeparator />
                                <DropdownMenuLabel class="px-2 py-1.5 text-xs text-muted-foreground">
                                    <span class="inline-flex items-center gap-1.5">
                                        <LayoutGrid class="size-3.5" />
                                        Panels
                                    </span>
                                </DropdownMenuLabel>
                                <DropdownMenuItem
                                    v-for="p in panels"
                                    :key="p.key"
                                    as-child
                                    class="cursor-pointer gap-2 rounded-lg"
                                >
                                    <Link :href="p.path">
                                        <Check
                                            class="size-4"
                                            :class="p.current ? 'opacity-100' : 'opacity-0'"
                                        />
                                        <span>{{ p.name }}</span>
                                    </Link>
                                </DropdownMenuItem>
                            </template>
                            <DropdownMenuSeparator />
                            <DropdownMenuLabel class="px-2 py-1.5 text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1.5">
                                    <Languages class="size-3.5" />
                                    Language
                                </span>
                            </DropdownMenuLabel>
                            <DropdownMenuItem
                                v-for="lang in languages"
                                :key="lang.locale"
                                class="cursor-pointer gap-2 rounded-lg"
                                @click="setLocale(lang.locale)"
                            >
                                <Check
                                    class="size-4"
                                    :class="locale === lang.locale ? 'opacity-100' : 'opacity-0'"
                                />
                                <span>{{ lang.label }}</span>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                class="cursor-pointer gap-2 rounded-lg text-destructive focus:text-destructive"
                                @click="logout()"
                            >
                                <LogOut class="size-4" />
                                Log out
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </aside>

            <div class="hidden w-64 shrink-0 lg:block" aria-hidden="true" />

            <div class="relative flex min-h-screen min-w-0 flex-1 flex-col">
                <Button
                    variant="outline"
                    size="icon-sm"
                    class="fixed start-4 bottom-4 z-30 rounded-xl shadow-sm lg:hidden"
                    @click="admin.sideBarOpen = !admin.sideBarOpen"
                >
                    <Menu class="size-4" />
                </Button>

                <main class="flex-1 overflow-y-auto p-4 pt-14 pb-24 md:p-6 md:pb-6 lg:p-8 lg:pt-8 lg:pb-8">
                    <div class="mx-auto w-full max-w-6xl">
                        <slot />
                    </div>
                </main>

                <!-- Target for Button::showOnBottomBar() (mobile only) -->
                <div
                    id="admin-mobile-bottom-bar"
                    class="fixed inset-x-0 bottom-0 z-40 hidden max-md:has-[button]:flex flex-wrap items-center justify-end gap-2 border-t border-border/80 bg-background/95 py-3 pe-4 ps-16 shadow-[0_-8px_30px_rgba(0,0,0,0.06)] backdrop-blur-md"
                />
            </div>
        </div>
    </div>
</template>
