<?php

namespace App\Http\Middleware;

use App\AdminPanel\Notifications\FlashBag;
use App\Models\PanelSetting;
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
     * Slim shared bag for the active panel (branding from DB, menu per panel).
     *
     * @return array<string, mixed>
     */
    protected function sharePanel(Request $request): array
    {
        $key = $request->attributes->get('admin.panel')
            ?? (app()->bound('admin.panel') ? app('admin.panel') : null)
            ?? $this->detectPanelKey($request)
            ?? config('admin.default', 'admin');

        // Ensure helpers (admin_path, admin_menu, …) resolve this panel during share.
        app()->instance('admin.panel', $key);

        $settings = PanelSetting::forPanel($key);
        $showMenu = (bool) $request->user();

        return [
            'key' => $key,
            'name' => $settings->app_name,
            'prefix' => admin_prefix($key),
            'path' => admin_path('', $key),
            'logo_url' => $settings->logo_url,
            'navbar_title' => $settings->navbar_title,
            'show_theme_toggle' => (bool) $settings->show_theme_toggle,
            'locale' => app()->getLocale(),
            'language' => admin_language(),
            'languages' => admin_languages(),
            'menu' => $showMenu ? admin_menu($key) : [],
        ];
    }

    protected function detectPanelKey(Request $request): ?string
    {
        $path = trim($request->path(), '/');

        foreach (config('admin.panels', []) as $key => $config) {
            $prefix = trim((string) ($config['prefix'] ?? ''), '/');
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix.'/'))) {
                return $key;
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
