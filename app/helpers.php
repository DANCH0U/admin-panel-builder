<?php

use App\AdminPanel\Panel;
use App\AdminPanel\PanelRegistry;
use Illuminate\Support\Str;

if (! function_exists('admin_panel')) {
    /**
     * Current admin panel key (set by ResolveAdminPanel middleware).
     */
    function admin_panel(): string
    {
        if (app()->bound('admin.panel')) {
            return (string) app('admin.panel');
        }

        return (string) config('admin.default', 'admin');
    }
}

if (! function_exists('admin_panel_instance')) {
    function admin_panel_instance(?string $panel = null): Panel
    {
        $panel ??= admin_panel();

        return PanelRegistry::get($panel);
    }
}

if (! function_exists('admin_panel_config')) {
    /**
     * @return array<string, mixed>
     */
    function admin_panel_config(?string $panel = null): array
    {
        try {
            return admin_panel_instance($panel)->toConfig();
        } catch (Throwable) {
            return [];
        }
    }
}

if (! function_exists('admin_prefix')) {
    /**
     * Configured admin URL prefix without leading/trailing slashes.
     */
    function admin_prefix(?string $panel = null): string
    {
        try {
            return admin_panel_instance($panel)->getPrefix();
        } catch (Throwable) {
            return trim((string) config('admin.prefix', 'admin'), '/');
        }
    }
}

if (! function_exists('admin_path')) {
    /**
     * Build a path under the current (or given) panel prefix.
     */
    function admin_path(string $path = '', ?string $panel = null): string
    {
        $prefix = admin_prefix($panel);
        $path = trim($path, '/');

        return $path === '' ? "/{$prefix}" : "/{$prefix}/{$path}";
    }
}

if (! function_exists('admin_url')) {
    /**
     * Absolute URL under the admin prefix.
     */
    function admin_url(string $path = '', ?string $panel = null): string
    {
        return url(admin_path($path, $panel));
    }
}

if (! function_exists('admin_home')) {
    /**
     * Path admins land on after login for the current panel.
     */
    function admin_home(?string $panel = null): string
    {
        try {
            $configured = admin_panel_instance($panel)->getHome();
        } catch (Throwable) {
            $configured = null;
        }

        if (is_string($configured) && $configured !== '') {
            return Str::startsWith($configured, '/') ? $configured : admin_path($configured, $panel);
        }

        return admin_path('', $panel);
    }
}

if (! function_exists('admin_menu')) {
    /**
     * Build sidebar menu for the current (or given) panel.
     *
     * @return list<array<string, mixed>>
     */
    function admin_menu(?string $panel = null): array
    {
        try {
            return admin_panel_instance($panel)->menu();
        } catch (Throwable) {
            return [];
        }
    }
}

if (! function_exists('notify')) {
    /**
     * Queue an Inertia toast notification (see App\AdminPanel\Notifications\Notify).
     *
     * notify('success', 'Saved')->action('View', admin_path('tests'));
     */
    function notify(string $type, string $message): \App\AdminPanel\Notifications\Notify
    {
        return \App\AdminPanel\Notifications\Notify::make($type, $message)->send();
    }
}

if (! function_exists('admin_languages')) {
    /**
     * @return list<array{label: string, locale: string, font: string, family?: string}>
     */
    function admin_languages(): array
    {
        return array_values(config('admin.languages', []));
    }
}

if (! function_exists('admin_locales')) {
    /**
     * @return list<string>
     */
    function admin_locales(): array
    {
        return array_values(array_map(
            fn (array $lang) => (string) ($lang['locale'] ?? ''),
            admin_languages(),
        ));
    }
}

if (! function_exists('admin_language')) {
    /**
     * @return array{label: string, locale: string, font: string, family?: string}|null
     */
    function admin_language(?string $locale = null): ?array
    {
        $locale ??= app()->getLocale();

        foreach (admin_languages() as $language) {
            if (($language['locale'] ?? null) === $locale) {
                return $language;
            }
        }

        return admin_languages()[0] ?? null;
    }
}

if (! function_exists('admin_font_family')) {
    /**
     * Font family name for the active (or given) language.
     */
    function admin_font_family(?string $fontUrl = null, ?array $language = null): string
    {
        $language ??= admin_language();

        if (is_array($language) && filled($language['family'] ?? null)) {
            return (string) $language['family'];
        }

        $fontUrl ??= $language['font'] ?? '';

        if (preg_match('/family=([^:&]+)/', $fontUrl, $matches)) {
            return str_replace('+', ' ', urldecode($matches[1]));
        }

        return 'Plus Jakarta Sans';
    }
}
