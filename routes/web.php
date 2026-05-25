<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/send', [DashboardController::class, 'send'])->name('send');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/login', [SettingsController::class, 'login'])->name('settings.login');
Route::post('/settings/save', [SettingsController::class, 'save'])->name('settings.save');
Route::post('/settings/logout', [SettingsController::class, 'logout'])->name('settings.logout');

// One-time migration runner (protected by SETTINGS_PASSWORD)
Route::get('/migrate', function () {
    if (env('SETTINGS_PASSWORD') && request('key') !== env('SETTINGS_PASSWORD')) {
        abort(403);
    }
    Artisan::call('migrate', ['--force' => true]);
    return '<pre>' . Artisan::output() . '</pre>';
});
