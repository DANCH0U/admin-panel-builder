<?php

namespace App\Providers;

use App\AdminPanel\PanelRegistry;
use App\AdminPanel\Panels\VendorPanel;
use App\AdminPanel\Panels\AdminPanel;
use Illuminate\Support\ServiceProvider;

class AdminPanelProvider extends ServiceProvider
{
    /**
     * Panel classes under App\AdminPanel\Panels (settings + menu).
     *
     * Register panels created with `php artisan make:admin-panel {name}`.
     *
     * @var list<class-string<\App\AdminPanel\Panel>>
     */
    protected array $panels = [
        AdminPanel::class,
        VendorPanel::class,
    ];

    public function register(): void
    {
        foreach ($this->panels as $panel) {
            PanelRegistry::register($panel);
        }
    }

    public function boot(): void
    {
        //
    }
}
