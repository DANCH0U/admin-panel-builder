<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
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
import NotificationsBell from '@/components/Admin/NotificationsBell.vue';
import AdminLoadingIndicator from '@/components/Admin/AdminLoadingIndicator.vue';
import { useAdminConfig } from '@/composables/useAdminConfig';
import { useAuth } from '@/composables/useAuth';
import { useShellData } from '@/composables/useShellData';
import { useAdminStore } from '@/stores/useAdminStore';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Check,
    ChevronDown,
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
const ui = computed(() => (panelProps.value.ui as Record<string, string>) || {});

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
    (menu.value as any[]).filter(
        (item) => item?.type !== 'label' && !item?.disabled && (item?.url || item?.href),
    ),
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
    return String(item.title || item.label || item.key || 'Item');
}

function suffixColor(color?: string) {
    const map: Record<string, string> = {
        danger: 'danger',
        destructive: 'danger',
        success: 'success',
        warning: 'warning',
        info: 'info',
        secondary: 'secondary',
        outline: 'outline',
        default: 'default',
    };
    return map[color || 'default'] ?? 'default';
}

function avatarSrc(avatar?: string | null) {
    if (!avatar) return null;
    if (avatar.startsWith('http://') || avatar.startsWith('https://') || avatar.startsWith('/')) {
        return avatar;
    }
    return `/storage/${avatar.replace(/^\/+/, '')}`;
}

const userAvatar = computed(() => avatarSrc((user.value as any)?.avatar));
const userInitial = computed(() => user.value?.name?.charAt(0)?.toUpperCase() || 'A');

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

        <header
            class="sticky top-0 z-40 border-b border-border/80 bg-background/85 backdrop-blur supports-[backdrop-filter]:bg-background/70">
            <div class="flex h-14 items-center gap-2 px-4 md:px-6">
                <Button variant="ghost" size="icon-sm" class="lg:hidden" aria-label="Toggle navigation"
                    @click="admin.sideBarOpen = !admin.sideBarOpen">
                    <Menu class="size-4" />
                </Button>

                <Link :href="adminPath()" class="flex min-w-0 items-center gap-3">
                    <img v-if="logoUrl" :src="logoUrl" alt=""
                        class="h-9 w-9 shrink-0 rounded-xl object-contain ring-1 ring-border" />
                    <span v-else
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary text-sm font-bold text-primary-foreground">
                        {{ appName.charAt(0).toUpperCase() }}
                    </span>
                    <span class="truncate text-[15px] font-semibold tracking-tight">{{
                        appName
                        }}</span>
                </Link>

                <div class="ms-auto flex items-center gap-1.5">
                    <Button variant="ghost" size="icon-sm" class="rounded-xl" :aria-label="dark ? ui.light : ui.dark"
                        :title="dark ? ui.light : ui.dark" @click="toggleTheme">
                        <Sun v-if="dark" class="size-4" />
                        <Moon v-else class="size-4" />
                    </Button>
                    <NotificationsBell :labels="ui" />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button type="button"
                                class="flex items-center gap-2.5 rounded-xl border border-transparent p-1.5 text-left transition-colors hover:border-border hover:bg-accent sm:ps-2.5">
                                <div class="hidden min-w-0 sm:block">
                                    <p class="truncate text-sm font-medium leading-tight">
                                        {{ user?.name }}
                                    </p>
                                    <p class="truncate text-xs leading-tight text-muted-foreground">
                                        {{ user?.email }}
                                    </p>
                                </div>
                                <Avatar class="size-9 ring-1 ring-border">
                                    <AvatarImage v-if="userAvatar" :src="userAvatar" :alt="user?.name || 'Avatar'" />
                                    <AvatarFallback>
                                        {{ userInitial }}
                                    </AvatarFallback>
                                </Avatar>
                                <ChevronDown class="size-4 shrink-0 text-muted-foreground" />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" :side-offset="8" class="w-56 rounded-xl p-1.5 shadow-lg">
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
                                    {{ ui.profile }}
                                </Link>
                            </DropdownMenuItem>
                            <template v-if="panels.length">
                                <DropdownMenuSeparator />
                                <DropdownMenuLabel class="px-2 py-1.5 text-xs text-muted-foreground">
                                    <span class="inline-flex items-center gap-1.5">
                                        <LayoutGrid class="size-3.5" />
                                        {{ ui.panels }}
                                    </span>
                                </DropdownMenuLabel>
                                <DropdownMenuItem v-for="p in panels" :key="p.key" as-child
                                    class="cursor-pointer gap-2 rounded-lg">
                                    <Link :href="p.path">
                                        <Check class="size-4" :class="p.current ? 'opacity-100' : 'opacity-0'" />
                                        <span>{{ p.name }}</span>
                                    </Link>
                                </DropdownMenuItem>
                            </template>
                            <DropdownMenuSeparator />
                            <DropdownMenuLabel class="px-2 py-1.5 text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1.5">
                                    <Languages class="size-3.5" />
                                    {{ ui.language }}
                                </span>
                            </DropdownMenuLabel>
                            <DropdownMenuItem v-for="lang in languages" :key="lang.locale"
                                class="cursor-pointer gap-2 rounded-lg" @click="setLocale(lang.locale)">
                                <Check class="size-4" :class="locale === lang.locale ? 'opacity-100' : 'opacity-0'" />
                                <span>{{ lang.label }}</span>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                class="cursor-pointer gap-2 rounded-lg text-destructive focus:text-destructive"
                                @click="logout()">
                                <LogOut class="size-4" />
                                {{ ui.logout }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>

        <div class="flex">
            <div v-if="admin.sideBarOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden"
                @click="admin.sideBarOpen = false" />

            <aside :class="cn(
                'fixed inset-y-0 start-0 z-50 flex w-64 flex-col border-e border-sidebar-border bg-sidebar text-sidebar-foreground shadow-sm transition-transform lg:top-14 lg:z-30 lg:shadow-none',
                admin.sideBarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0',
            )
                ">
                <div class="flex h-16 shrink-0 items-center gap-3 px-4 lg:hidden">
                    <span class="truncate text-[15px] font-semibold tracking-tight">{{
                        appName
                        }}</span>
                    <Button variant="ghost" size="icon-sm" class="ms-auto" aria-label="Close navigation"
                        @click="admin.sideBarOpen = false">
                        <X class="size-4" />
                    </Button>
                </div>

                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 pb-3 lg:pt-4">
                    <template v-for="(item, index) in menu" :key="index">
                        <div v-if="item.type === 'label'"
                            class="px-2 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
                            {{ menuLabel(item) }}
                        </div>
                        <div v-else-if="item.disabled" :class="cn(
                            'flex cursor-not-allowed items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm opacity-50',
                            'text-sidebar-foreground/60',
                        )
                            ">
                            <Icon v-if="item.icon" :icon="item.icon" class="size-5 shrink-0 opacity-70" />
                            <span class="min-w-0 flex-1 truncate">{{ menuLabel(item) }}</span>
                            <Badge v-if="item.suffix?.type === 'badge'" :variant="suffixColor(item.suffix.color) as any"
                                class="ms-auto shrink-0 px-1.5 py-0">
                                {{ item.suffix.value }}
                            </Badge>
                            <Icon v-else-if="item.suffix?.type === 'icon'" :icon="item.suffix.value"
                                class="ms-auto size-4 shrink-0 opacity-70" />
                        </div>
                        <Link v-else :href="item.url || item.href || '#'" :class="cn(
                            'flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm transition-all',
                            isActive(item.url || item.href)
                                ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground'
                                : 'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                        )
                            " @click="admin.sideBarOpen = false">
                            <Icon v-if="item.icon" :icon="item.icon" class="size-5 shrink-0 opacity-70" />
                            <span class="min-w-0 flex-1 truncate">{{ menuLabel(item) }}</span>
                            <Badge v-if="item.suffix?.type === 'badge'" :variant="suffixColor(item.suffix.color) as any"
                                class="ms-auto shrink-0 px-1.5 py-0">
                                {{ item.suffix.value }}
                            </Badge>
                            <Icon v-else-if="item.suffix?.type === 'icon'" :icon="item.suffix.value"
                                class="ms-auto size-4 shrink-0 opacity-70" />
                        </Link>
                    </template>
                </nav>
            </aside>

            <div class="hidden w-64 shrink-0 lg:block" aria-hidden="true" />

            <div class="relative flex min-h-[calc(100vh-4rem)] min-w-0 flex-1 flex-col">
                <main class="flex-1 p-4 pb-24 md:p-6 md:pb-6 lg:p-8">
                    <div class="mx-auto w-full max-w-6xl">
                        <slot />
                    </div>
                </main>

                <!-- Target for Button::showOnBottomBar() (mobile only) -->
                <div id="admin-mobile-bottom-bar"
                    class="fixed inset-x-0 bottom-0 z-40 hidden max-md:has-[button]:flex flex-wrap items-center justify-end gap-2 border-t border-border/80 bg-background/95 px-4 py-3 shadow-[0_-8px_30px_rgba(0,0,0,0.06)] backdrop-blur-md" />
            </div>
        </div>
    </div>
</template>
