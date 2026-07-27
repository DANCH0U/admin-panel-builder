<?php

namespace App\Http\Middleware;

use App\AdminPanel\Notifications\FlashBag;
use App\AdminPanel\PanelRegistry;
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
            ?? $this->detectPanelKey($request);

        if (! is_string($key) || $key === '') {
            try {
                $key = PanelRegistry::default()->getId();
            } catch (\Throwable) {
                $key = 'admin';
            }
        }

        app()->instance('admin.panel', $key);

        $showMenu = (bool) $request->user();

        if (! PanelRegistry::has($key)) {
            return [
                'key' => $key,
                'name' => 'Admin Panel',
                'prefix' => admin_prefix($key),
                'path' => admin_path('', $key),
                'logo_url' => null,
                'locale' => app()->getLocale(),
                'language' => admin_language(),
                'languages' => admin_languages(),
                'ui' => $this->shareUiStrings(),
                'menu' => [],
                'panels' => $showMenu ? $this->sharePanelsList($key) : [],
                'loading_delay_ms' => (int) config('admin.ui.loading_delay_ms', 200),
            ];
        }

        $panel = PanelRegistry::get($key);

        return [
            'key' => $key,
            'name' => $panel->getName(),
            'prefix' => $panel->getPrefix(),
            'path' => admin_path('', $key),
            'logo_url' => $panel->getLogo(),
            'locale' => app()->getLocale(),
            'language' => admin_language(),
            'languages' => admin_languages(),
            'ui' => $this->shareUiStrings(),
            'menu' => $showMenu ? admin_menu($key) : [],
            'panels' => $showMenu ? $this->sharePanelsList($key) : [],
            'loading_delay_ms' => (int) config('admin.ui.loading_delay_ms', 200),
        ];
    }

    /**
     * Shell chrome labels — already resolved with __() (not a translation bag).
     *
     * @return array<string, string>
     */
    protected function shareUiStrings(): array
    {
        return [
            'profile' => __('admin.profile'),
            'panels' => __('admin.panels'),
            'language' => __('admin.language'),
            'logout' => __('admin.logout'),
            'light' => __('admin.light'),
            'dark' => __('admin.dark'),
        ];
    }

    /**
     * Visible panels for the user-menu switcher (excludes ->hidden()).
     *
     * @return list<array{key: string, name: string, path: string, current: bool}>
     */
    protected function sharePanelsList(string $currentKey): array
    {
        $list = [];

        foreach (PanelRegistry::all() as $panel) {
            if ($panel->isHidden()) {
                continue;
            }

            $id = $panel->getId();

            $list[] = [
                'key' => $id,
                'name' => $panel->getName(),
                'path' => admin_path('', $id),
                'current' => $id === $currentKey,
            ];
        }

        return $list;
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

}
