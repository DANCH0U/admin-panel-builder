<?php

namespace App\AdminPanel\Panels;

use App\AdminPanel\Menu\PanelMenu;
use App\AdminPanel\Panel;

class AdminPanel extends Panel
{
    public function __construct()
    {
        parent::__construct('admin');

        $this
            ->prefix(env('ADMIN_PREFIX', 'admin'))
            ->middleware(['auth', 'admin', 'panel:admin'])
            ->name(env('ADMIN_NAME', 'Admin Panel'))
            ->logo(env('ADMIN_LOGO_URL', '/admin-logo.svg'))
            ->navbarTitle(env('ADMIN_NAVBAR_TITLE', 'Admin Panel'))
            ->showThemeToggle(true);
    }

    public function menu(): array
    {
        return PanelMenu::make()
            ->default()
            ->build();
    }
}
