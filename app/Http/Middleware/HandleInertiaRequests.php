<?php

namespace App\Http\Middleware;

use App\AdminPanel\Notifications\FlashBag;
use App\AdminPanel\PanelRegistry;
use App\Support\CachedTranslations;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user(),
            ],
            'translations' => fn () => $this->getTranslations($request),
            'notifications' => fn () => FlashBag::collect(),
            'panel' => fn () => $this->sharePanel($request),
        ];
    }

    /**
     * Slim shared bag for the active panel (branding + menu from PanelRegistry).
     *
     * @return array<string, mixed>
     */
    protected function sharePanel(Request $request): array
    {
        $key = $request->attributes->get('admin.panel')
            ?? (app()->bound('admin.panel') ? app('admin.panel') : null)
            ?? $this->detectPanelKey($request)
            ?? config('admin.default', 'admin');

        app()->instance('admin.panel', $key);

        $showMenu = (bool) $request->user();

        if (! PanelRegistry::has($key)) {
            return [
                'key' => $key,
                'name' => config('admin.name', 'Admin Panel'),
                'prefix' => admin_prefix($key),
                'path' => admin_path('', $key),
                'logo_url' => null,
                'navbar_title' => null,
                'show_theme_toggle' => true,
                'locale' => app()->getLocale(),
                'language' => admin_language(),
                'languages' => admin_languages(),
                'menu' => [],
            ];
        }

        $panel = PanelRegistry::get($key);

        return [
            'key' => $key,
            'name' => $panel->getName(),
            'prefix' => $panel->getPrefix(),
            'path' => admin_path('', $key),
            'logo_url' => $panel->getLogo(),
            'navbar_title' => $panel->getNavbarTitle(),
            'show_theme_toggle' => $panel->getShowThemeToggle(),
            'locale' => app()->getLocale(),
            'language' => admin_language(),
            'languages' => admin_languages(),
            'menu' => $showMenu ? admin_menu($key) : [],
        ];
    }

    protected function detectPanelKey(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        foreach (PanelRegistry::all() as $panel) {
            $prefix = $panel->getPrefix();
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix.'/'))) {
                return $panel->getId();
            }
        }

        return null;
    }

    protected function getTranslations(Request $request): array
    {
        $locale = app()->getLocale();

        $translations = [
            'content' => CachedTranslations::get('content', $locale),
        ];

        if ($request->user()?->isAdmin()) {
            $translations['admin'] = CachedTranslations::get('admin', $locale);
        }

        return $translations;
    }
}
