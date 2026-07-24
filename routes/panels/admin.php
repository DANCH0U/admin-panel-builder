<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel panel (`admin`) routes
|--------------------------------------------------------------------------
|
| Prefix and middleware come from PanelRegistry (AdminPanelProvider).
|
*/

Route::get('/', DashboardController::class)->name('admin.dashboard');

Route::post('/posts/bulk', [\App\Http\Controllers\Admin\PostController::class, 'bulk'])
    ->name('posts.bulk');
Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);

Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])
    ->name('admin.profile.edit');
Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])
    ->name('admin.profile.update');
Route::delete('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'destroy'])
    ->name('admin.profile.destroy');
