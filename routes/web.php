<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/admin.php';

Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/locale/{locale}', \App\Http\Controllers\LocaleController::class)->name('locale.set');

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
});

Route::inertia('/', 'Home')->name('home');
