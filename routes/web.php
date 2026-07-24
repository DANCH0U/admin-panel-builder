<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/admin.php';

Route::post('/secure-download/{filepath}', [FileController::class, 'secureDownload'])
    ->where('filepath', '.*');

Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/locale/{locale}', \App\Http\Controllers\LocaleController::class)->name('locale.set');

Route::inertia('/', 'Home')->name('home');
