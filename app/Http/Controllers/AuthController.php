<?php

namespace App\Http\Controllers;

use App\Models\DiscordUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function pending(): View
    {
        return view('auth.pending');
    }

    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        $query = http_build_query([
            'client_id'     => config('discord.application_id'),
            'redirect_uri'  => url('/auth/discord/callback'),
            'response_type' => 'code',
            'scope'         => 'identify',
            'state'         => $state,
            'prompt'        => 'none',
        ]);

        return redirect('https://discord.com/api/oauth2/authorize?' . $query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->input('state') !== $request->session()->pull('oauth_state')) {
            return redirect()->route('login')->withErrors(['oauth' => 'Estado inválido. Inténtalo de nuevo.']);
        }

        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['oauth' => 'Acceso cancelado en Discord.']);
        }

        $redirectUri = url('/auth/discord/callback');
        \Illuminate\Support\Facades\Log::debug('OAuth callback', [
            'redirect_uri'    => $redirectUri,
            'has_client_id'   => (bool) config('discord.application_id'),
            'has_client_secret' => (bool) config('discord.client_secret'),
        ]);

        // Exchange code → access token
        $tokenRes = Http::asForm()->post('https://discord.com/api/v10/oauth2/token', [
            'client_id'     => config('discord.application_id'),
            'client_secret' => config('discord.client_secret'),
            'grant_type'    => 'authorization_code',
            'code'          => $request->input('code'),
            'redirect_uri'  => $redirectUri,
        ]);

        if (!$tokenRes->ok()) {
            $errBody = $tokenRes->json();
            return redirect()->route('login')->withErrors([
                'oauth' => 'Error al obtener token de Discord: ' . ($errBody['error_description'] ?? $errBody['error'] ?? $tokenRes->status()),
            ]);
        }

        // Get Discord user info
        $userRes = Http::withToken($tokenRes->json('access_token'))->get('https://discord.com/api/v10/users/@me');

        if (!$userRes->ok()) {
            return redirect()->route('login')->withErrors(['oauth' => 'Error al obtener datos del usuario de Discord.']);
        }

        $data      = $userRes->json();
        $discordId = $data['id'];
        $username  = $data['username'];
        $avatar    = $data['avatar'];
        $adminId   = config('discord.admin_id');
        $isAdmin   = $adminId && $discordId === $adminId;

        // Find or create — never overwrite approved status unless becoming admin
        $user = DiscordUser::find($discordId);
        if ($user) {
            $user->username = $username;
            $user->avatar   = $avatar;
            if ($isAdmin) {
                $user->is_admin = true;
                $user->approved = true;
            }
            $user->save();
        } else {
            $user = DiscordUser::create([
                'discord_id' => $discordId,
                'username'   => $username,
                'avatar'     => $avatar,
                'approved'   => $isAdmin,
                'is_admin'   => $isAdmin,
            ]);
        }

        $request->session()->put('discord_user_id', $discordId);

        if (!$user->approved) {
            return redirect()->route('auth.pending');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('discord_user_id');
        return redirect()->route('login');
    }
}
