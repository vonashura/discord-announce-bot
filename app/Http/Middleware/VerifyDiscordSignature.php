<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDiscordSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Signature-Ed25519');
        $timestamp  = $request->header('X-Signature-Timestamp');
        $body       = $request->getContent();
        $publicKey  = config('discord.public_key');

        if (!$signature || !$timestamp || !$publicKey) {
            abort(401, 'Missing Discord signature headers');
        }

        try {
            $valid = sodium_crypto_sign_verify_detached(
                hex2bin($signature),
                $timestamp . $body,
                hex2bin($publicKey)
            );
        } catch (\Exception) {
            abort(401, 'Invalid signature format');
        }

        if (!$valid) {
            abort(401, 'Invalid request signature');
        }

        return $next($request);
    }
}
