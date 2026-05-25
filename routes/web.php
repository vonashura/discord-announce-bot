<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Serve compiled assets through PHP (vercel-php routes all requests to the lambda)
Route::get('/build/{path}', function (string $path) {
    $file = public_path('build/' . $path);
    if (!is_file($file)) abort(404);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = match($ext) {
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'woff2'=> 'font/woff2',
        'woff' => 'font/woff',
        'ttf'  => 'font/ttf',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        default=> 'application/octet-stream',
    };
    return response(file_get_contents($file), 200, [
        'Content-Type'  => $mime,
        'Cache-Control' => 'public, immutable, max-age=31536000',
    ]);
})->where('path', '.+');

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
