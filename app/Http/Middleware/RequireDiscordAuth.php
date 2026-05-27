<?php

namespace App\Http\Middleware;

use App\Models\DiscordUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDiscordAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $discordId = $request->session()->get('discord_user_id');

        if (!$discordId) {
            return redirect()->route('login');
        }

        $user = DiscordUser::find($discordId);

        if (!$user) {
            $request->session()->forget('discord_user_id');
            return redirect()->route('login');
        }

        if (!$user->approved) {
            return redirect()->route('auth.pending');
        }

        // Share with all views in this request
        view()->share('authUser', $user);

        return $next($request);
    }
}
