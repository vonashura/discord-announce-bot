<?php

namespace App\Http\Middleware;

use App\Services\DiscordService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDiscordSignature
{
    public function __construct(private DiscordService $discord) {}

    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Signature-Ed25519');
        $timestamp  = $request->header('X-Signature-Timestamp');
        $body       = $request->getContent();
        $publicKey  = $this->discord->getPublicKey();

        if (!$signature || !$timestamp || !$publicKey) {
            abort(401, 'Missing Discord signature headers or public key not configured');
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
