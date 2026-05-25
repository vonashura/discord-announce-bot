<?php

use App\Http\Controllers\DiscordController;
use Illuminate\Support\Facades\Route;

// Discord Interactions Endpoint
// Configurar en: Discord Developer Portal → Applications → [tu app] → Interactions Endpoint URL
// URL: https://tu-dominio.com/api/discord/interactions
Route::post('/discord/interactions', [DiscordController::class, 'handle'])
    ->middleware('discord.verify');

// POST echo — diagnose headers/body/sig (remove after debugging)
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
