<?php

use App\AdminPanel\PanelRegistry;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
|
| Each registered panel loads routes/panels/{id}.php under its prefix
| and middleware from PanelRegistry (see AdminPanelProvider).
|
*/

foreach (PanelRegistry::all() as $panel) {
    $file = base_path("routes/panels/{$panel->getId()}.php");

    if (! is_file($file)) {
        continue;
    }

    Route::middleware($panel->getMiddleware())
        ->prefix($panel->getPrefix())
        ->group($file);
}
