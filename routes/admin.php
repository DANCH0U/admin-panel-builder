<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel routes
|--------------------------------------------------------------------------
|
| Each panel in config/admin.php panels[] can have its own route file.
| Middleware includes `panel:{key}` so helpers + settings resolve correctly.
|
*/

$panelKey = 'admin';
$panel = config("admin.panels.{$panelKey}", []);
$prefix = trim((string) ($panel['prefix'] ?? 'admin'), '/');
$middleware = $panel['middleware'] ?? ['auth', 'admin', "panel:{$panelKey}"];

Route::middleware($middleware)->prefix($prefix)->group(function () {
    Route::get('/', DashboardController::class)->name('admin.dashboard');
    Route::post('/tests/bulk', [\App\Http\Controllers\Admin\TestController::class, 'bulk'])
        ->name('tests.bulk');
    Route::resource('tests', \App\Http\Controllers\Admin\TestController::class);

    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'edit'])
        ->name('admin.settings.edit');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])
        ->name('admin.settings.update');

    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])
        ->name('admin.profile.edit');
    Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])
        ->name('admin.profile.update');
    Route::delete('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'destroy'])
        ->name('admin.profile.destroy');

    Route::get('/demo/notifications/{type}', [\App\Http\Controllers\Admin\NotificationDemoController::class, 'flash'])
        ->whereIn('type', ['success', 'info', 'warning', 'danger'])
        ->name('demo.notifications');
});
