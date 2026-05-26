<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Vercel strips /api prefix from REQUEST_URI before PHP receives it,
// so routes at external path /api/discord/* must be defined here without /api prefix.
Route::post('/discord/interactions', [DiscordController::class, 'handle'])
    ->middleware('discord.verify');

Route::post('/discord-echo', function (\Illuminate\Http\Request $request) {
    $sig       = $request->header('X-Signature-Ed25519');
    $ts        = $request->header('X-Signature-Timestamp');
    $body      = $request->getContent();
    $publicKey = app(\App\Services\DiscordService::class)->getPublicKey();

    $verified = false;
    $verifyError = null;
    if ($sig && $ts && $publicKey) {
        try {
            $verified = sodium_crypto_sign_verify_detached(
                hex2bin($sig),
                $ts . $body,
                hex2bin($publicKey)
            );
        } catch (\Exception $e) {
            $verifyError = $e->getMessage();
        }
    }

    return response()->json([
        'has_sig_header' => (bool) $sig,
        'has_ts_header'  => (bool) $ts,
        'body_length'    => strlen($body),
        'body_preview'   => substr($body, 0, 200),
        'body_type'      => $request->header('Content-Type'),
        'verified'       => $verified,
        'verify_error'   => $verifyError,
        'all_headers'    => collect($request->headers->all())
                            ->only(['x-signature-ed25519', 'x-signature-timestamp', 'content-type', 'user-agent'])
                            ->toArray(),
    ]);
});

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/send', [DashboardController::class, 'send'])->name('send');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::post('/settings/login', [SettingsController::class, 'login'])->name('settings.login');
Route::post('/settings/save', [SettingsController::class, 'save'])->name('settings.save');
Route::post('/settings/logout', [SettingsController::class, 'logout'])->name('settings.logout');

// Discord endpoint diagnostic (no auth required — remove after debugging)
Route::get('/discord-debug', function () {
    $discord = app(\App\Services\DiscordService::class);
    $publicKey = $discord->getPublicKey();
    return response()->json([
        'sodium_available'    => function_exists('sodium_crypto_sign_verify_detached'),
        'public_key_source'   => $publicKey ? 'ok' : 'MISSING',
        'public_key_preview'  => $publicKey ? substr($publicKey, 0, 8) . '...' : null,
        'db_connection'       => (function () {
            try { \Illuminate\Support\Facades\DB::connection()->getPdo(); return 'ok'; }
            catch (\Exception $e) { return $e->getMessage(); }
        })(),
    ]);
});


Route::get('/debug-assets', function () {
    $p = public_path('build');
    return response()->json([
        'public_path' => $p,
        'exists' => is_dir($p),
        'files' => is_dir($p) ? array_slice(scandir($p), 2) : [],
        'assets' => is_dir("$p/assets") ? scandir("$p/assets") : 'no assets dir',
    ]);
});

// Register Discord slash commands (protected by SETTINGS_PASSWORD)
Route::get('/register-commands', function () {
    if (env('SETTINGS_PASSWORD') && request('key') !== env('SETTINGS_PASSWORD')) {
        abort(403);
    }
    Artisan::call('discord:register-commands');
    return '<pre>' . Artisan::output() . '</pre>';
});

// One-time migration runner (protected by SETTINGS_PASSWORD)
Route::get('/migrate', function () {
    if (env('SETTINGS_PASSWORD') && request('key') !== env('SETTINGS_PASSWORD')) {
        abort(403);
    }
    Artisan::call('migrate', ['--force' => true]);
    return '<pre>' . Artisan::output() . '</pre>';
});
