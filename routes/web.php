<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// ── Discord interactions (public — verified by signature middleware) ──────────
// Vercel strips /api prefix from REQUEST_URI before PHP receives it,
// so the external path /api/discord/* is defined here without the /api prefix.
Route::post('/discord/interactions', [DiscordController::class, 'handle'])
    ->middleware('discord.verify');

// ── Auth (public) ─────────────────────────────────────────────────────────────
Route::get('/login',                  [AuthController::class, 'showLogin'])->name('login');
Route::get('/auth/discord',           [AuthController::class, 'redirect'])->name('auth.discord');
Route::get('/auth/discord/callback',  [AuthController::class, 'callback'])->name('auth.discord.callback');
Route::get('/auth/pending',           [AuthController::class, 'pending'])->name('auth.pending');
Route::post('/auth/logout',           [AuthController::class, 'logout'])->name('auth.logout');

// ── Protected: requires Discord login + approved ───────────────────────────────
Route::middleware('auth.discord')->group(function () {

    Route::get('/',     [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/send',[DashboardController::class, 'send'])->name('send');

    // Admin only
    Route::middleware('auth.admin')->group(function () {

        Route::get('/admin/users',                          [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users/{id}/approve',            [AdminController::class, 'approve'])->name('admin.approve');
        Route::post('/admin/users/{id}/revoke',             [AdminController::class, 'revoke'])->name('admin.revoke');

        Route::get('/settings',         [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/save',   [SettingsController::class, 'save'])->name('settings.save');
    });
});

// ── Debug / ops routes (protected by SETTINGS_PASSWORD key param) ─────────────
Route::get('/discord-debug', function () {
    $discord    = app(\App\Services\DiscordService::class);
    $publicKey  = $discord->getPublicKey();
    $announceCh = $discord->getAnnouncementChannelId();
    $fortniteCh = $discord->getFortniteChannelId();
    $settingsOk = false;
    $usersOk    = false;
    try { \Illuminate\Support\Facades\DB::table('settings')->limit(1)->get();      $settingsOk = true; } catch (\Exception) {}
    try { \Illuminate\Support\Facades\DB::table('discord_users')->limit(1)->get(); $usersOk    = true; } catch (\Exception) {}
    return response()->json([
        'sodium_available'        => function_exists('sodium_crypto_sign_verify_detached'),
        'public_key_source'       => $publicKey  ? 'ok' : 'MISSING',
        'public_key_preview'      => $publicKey  ? substr($publicKey, 0, 8) . '...' : null,
        'announcement_channel_id' => $announceCh ? 'ok (' . substr($announceCh, 0, 4) . '...)' : 'MISSING',
        'fortnite_channel_id'     => $fortniteCh ? 'ok (' . substr($fortniteCh, 0, 4) . '...)' : 'MISSING',
        'client_secret_set'       => config('discord.client_secret') ? 'ok' : 'MISSING',
        'admin_id_set'            => config('discord.admin_id') ? 'ok (' . substr(config('discord.admin_id'), 0, 4) . '...)' : 'MISSING',
        'settings_table'          => $settingsOk ? 'exists' : 'MISSING (run /migrate)',
        'discord_users_table'     => $usersOk    ? 'exists' : 'MISSING (run /migrate)',
        'db_connection'           => (function () {
            try { \Illuminate\Support\Facades\DB::connection()->getPdo(); return 'ok'; }
            catch (\Exception $e) { return $e->getMessage(); }
        })(),
    ]);
});

Route::get('/debug-assets', function () {
    $p = public_path('build');
    return response()->json([
        'public_path' => $p,
        'exists'  => is_dir($p),
        'files'   => is_dir($p) ? array_slice(scandir($p), 2) : [],
        'assets'  => is_dir("$p/assets") ? scandir("$p/assets") : 'no assets dir',
    ]);
});

Route::get('/discord-echo', function (\Illuminate\Http\Request $request) {
    $sig      = $request->header('X-Signature-Ed25519');
    $ts       = $request->header('X-Signature-Timestamp');
    $body     = $request->getContent();
    $pubKey   = app(\App\Services\DiscordService::class)->getPublicKey();
    $verified = false; $verifyError = null;
    if ($sig && $ts && $pubKey) {
        try { $verified = sodium_crypto_sign_verify_detached(hex2bin($sig), $ts . $body, hex2bin($pubKey)); }
        catch (\Exception $e) { $verifyError = $e->getMessage(); }
    }
    return response()->json([
        'has_sig_header' => (bool) $sig, 'has_ts_header' => (bool) $ts,
        'body_length' => strlen($body), 'verified' => $verified, 'verify_error' => $verifyError,
    ]);
});

Route::get('/register-commands', function () {
    if (env('SETTINGS_PASSWORD') && request('key') !== env('SETTINGS_PASSWORD')) { abort(403); }
    Artisan::call('discord:register-commands');
    return '<pre>' . Artisan::output() . '</pre>';
});

Route::get('/migrate', function () {
    if (env('SETTINGS_PASSWORD') && request('key') !== env('SETTINGS_PASSWORD')) { abort(403); }
    Artisan::call('migrate', ['--force' => true]);
    return '<pre>' . Artisan::output() . '</pre>';
});
