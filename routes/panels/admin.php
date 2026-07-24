<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel (`admin`) routes
|--------------------------------------------------------------------------
*/

Route::get('/', DashboardController::class)->name('admin.dashboard');

Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])
    ->name('admin.profile.edit');
Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])
    ->name('admin.profile.update');
Route::delete('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'destroy'])
    ->name('admin.profile.destroy');
