<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const FIELDS = [
        'discord_bot_token'              => ['label' => 'Bot Token',                    'secret' => true,  'placeholder' => 'Tu token del bot de Discord'],
        'discord_application_id'         => ['label' => 'Application ID',               'secret' => false, 'placeholder' => 'ID numérico de la aplicación'],
        'discord_public_key'             => ['label' => 'Public Key (Interactions)',     'secret' => true,  'placeholder' => 'Clave pública para verificar requests'],
        'discord_guild_id'               => ['label' => 'Guild ID (servidor)',           'secret' => false, 'placeholder' => 'ID del servidor (vacío = comandos globales)'],
        'discord_announcement_channel_id'=> ['label' => 'Canal de Anuncios ID',          'secret' => false, 'placeholder' => 'ID del canal de anuncios generales'],
        'discord_fortnite_channel_id'    => ['label' => 'Canal Fortnite ID',             'secret' => false, 'placeholder' => 'ID del canal de partidas Fortnite'],
        'discord_announce_role_id'       => ['label' => 'Rol Anuncios ID (opcional)',    'secret' => false, 'placeholder' => 'Se menciona al enviar anuncio general'],
        'discord_fortnite_role_id'       => ['label' => 'Rol Fortnite ID (opcional)',   'secret' => false, 'placeholder' => 'Se menciona al publicar partida Fortnite'],
    ];

    public function index(Request $request): View
    {
        if (!$this->authenticated($request)) {
            return view('settings.login');
        }

        $values = [];
        foreach (array_keys(self::FIELDS) as $key) {
            $values[$key] = Setting::get($key, '');
        }

        return view('settings.index', [
            'fields' => self::FIELDS,
            'values' => $values,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $password = env('SETTINGS_PASSWORD', '');

        if (!$password || $request->input('password') === $password) {
            $request->session()->put('settings_auth', true);
            return redirect()->route('settings');
        }

        return back()->withErrors(['password' => 'Contraseña incorrecta.']);
    }

    public function save(Request $request): RedirectResponse
    {
        abort_unless($this->authenticated($request), 403);

        foreach (array_keys(self::FIELDS) as $key) {
            $val = $request->input($key);
            // Only update if provided; ignore empty secrets to avoid clearing them accidentally
            if ($val !== null && $val !== '') {
                Setting::set($key, trim($val));
            }
        }

        return back()->with('success', '✅ Configuración guardada correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('settings_auth');
        return redirect()->route('settings');
    }

    private function authenticated(Request $request): bool
    {
        $password = env('SETTINGS_PASSWORD', '');
        if ($password === '') {
            return true; // Dev mode: no password required
        }
        return $request->session()->get('settings_auth') === true;
    }
}
