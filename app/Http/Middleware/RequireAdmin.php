<?php

namespace App\Http\Middleware;

use App\Models\DiscordUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $discordId = $request->session()->get('discord_user_id');
        $user      = $discordId ? DiscordUser::find($discordId) : null;

        if (!$user || !$user->is_admin) {
            abort(403, 'Acceso restringido a administradores.');
        }

        return $next($request);
    }
}
