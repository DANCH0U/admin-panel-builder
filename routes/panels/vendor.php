<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Panel panel (`vendor`) routes
|--------------------------------------------------------------------------
|
| Prefix and middleware come from PanelRegistry (AdminPanelProvider).
|
*/

Route::get('/', DashboardController::class)->name('vendor.dashboard');

Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])
    ->name('vendor.profile.edit');
Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])
    ->name('vendor.profile.update');
Route::delete('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'destroy'])
    ->name('vendor.profile.destroy');
