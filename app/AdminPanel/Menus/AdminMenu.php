<?php

namespace App\AdminPanel\Menus;

use App\AdminPanel\Menu\MenuItem;
use App\AdminPanel\Menu\PanelMenu;

/**
 * Sidebar menu for the default `admin` panel.
 */
class AdminMenu
{
    public static function build(): array
    {
        return PanelMenu::make()
            ->default()
            ->section('builder', [
                MenuItem::link('tests', admin_path('tests'))
                    ->icon('heroicons:beaker')
                    ->title('Tests'),
            ])
            ->section('settings', [
                MenuItem::link('panel_settings', admin_path('settings'))
                    ->icon('heroicons:cog-6-tooth'),
            ])
            ->build();
    }
}
