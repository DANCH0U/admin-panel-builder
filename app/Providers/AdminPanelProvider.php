<?php

namespace App\Providers;

use App\AdminPanel\Menus\AdminMenu;
use App\AdminPanel\Panel;
use App\AdminPanel\PanelRegistry;
use Illuminate\Support\ServiceProvider;

class AdminPanelProvider extends ServiceProvider
{
    public function register(): void
    {
        PanelRegistry::register('admin', function (Panel $panel) {
            $panel
                ->prefix(env('ADMIN_PREFIX', 'admin'))
                ->middleware(['auth', 'admin', 'panel:admin'])
                ->name(env('ADMIN_NAME', 'Admin Panel'))
                ->logo(env('ADMIN_LOGO_URL', '/admin-logo.svg'))
                ->navbarTitle(env('ADMIN_NAVBAR_TITLE', 'Admin Panel'))
                ->showThemeToggle(true)
                ->menu(AdminMenu::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
