<?php

namespace App\AdminPanel\Panels;

use App\AdminPanel\Menu\MenuItem;
use App\AdminPanel\Menu\PanelMenu;
use App\AdminPanel\Panel;

class VendorPanel extends Panel
{
    public function __construct()
    {
        parent::__construct('vendor');

        $this
            ->prefix('vendor')
            ->middleware(['auth', 'panel:vendor'])
            ->name('Vendor Panel')
            ->logo(null)
            ->navbarTitle('Vendor Panel')
            ->showThemeToggle(true);
            // ->hidden(); // hide from user-menu switcher (middleware still guards URL access)
    }

    public function menu(): array
    {
        return PanelMenu::make()
            ->default()
            // ->section('main', [
            //     MenuItem::link('items', admin_path('items'))
            //         ->icon('heroicons:rectangle-stack')
            //         ->title('Items'),
            // ])
            ->build();
    }
}
