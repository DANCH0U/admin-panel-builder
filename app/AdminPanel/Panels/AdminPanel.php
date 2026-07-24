<?php

namespace App\AdminPanel\Panels;

use App\AdminPanel\Menu\MenuItem;
use App\AdminPanel\Menu\PanelMenu;
use App\AdminPanel\Panel;

class AdminPanel extends Panel
{
    public function __construct()
    {
        parent::__construct('admin');

        $this
            ->prefix('admin')
            ->middleware(['auth', 'panel:admin'])
            ->name('Admin Panel')
            ->logo(null)
            ->navbarTitle('Admin Panel')
            ->showThemeToggle(true);
            // ->hidden(); // hide from user-menu switcher (middleware still guards URL access)
    }

    public function menu(): array
    {
        return PanelMenu::make()
            ->default()
            ->section('content', [
                MenuItem::link('posts', admin_path('posts'))
                    ->icon('heroicons:pencil-square')
                    ->title('Posts'),
            ])
            ->build();
    }
}
