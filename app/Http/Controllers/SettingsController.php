<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const FIELDS = [
        'discord_bot_token'               => ['label' => 'Bot Token',                   'secret' => true,  'placeholder' => 'Tu token del bot de Discord'],
        'discord_application_id'          => ['label' => 'Application ID',              'secret' => false, 'placeholder' => 'ID numérico de la aplicación'],
        'discord_public_key'              => ['label' => 'Public Key (Interactions)',    'secret' => true,  'placeholder' => 'Clave pública para verificar requests'],
        'discord_guild_id'                => ['label' => 'Guild ID (servidor)',          'secret' => false, 'placeholder' => 'ID del servidor (vacío = comandos globales)'],
        'discord_announcement_channel_id' => ['label' => 'Canal de Anuncios ID',         'secret' => false, 'placeholder' => 'ID del canal de anuncios generales'],
        'discord_fortnite_channel_id'     => ['label' => 'Canal Fortnite ID',            'secret' => false, 'placeholder' => 'ID del canal de partidas Fortnite'],
        'discord_announce_role_id'        => ['label' => 'Rol Anuncios ID (opcional)',   'secret' => false, 'placeholder' => 'Se menciona al enviar anuncio general'],
        'discord_fortnite_role_id'        => ['label' => 'Rol Fortnite ID (opcional)',  'secret' => false, 'placeholder' => 'Se menciona al publicar partida Fortnite'],
    ];

    public function index(): View
    {
        $values = [];
        foreach (array_keys(self::FIELDS) as $key) {
            $values[$key] = Setting::get($key, '');
        }

        return view('settings.index', [
            'fields' => self::FIELDS,
            'values' => $values,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        foreach (array_keys(self::FIELDS) as $key) {
            $val = $request->input($key);
            if ($val !== null && $val !== '') {
                Setting::set($key, trim($val));
            }
        }

        return back()->with('success', '✅ Configuración guardada correctamente.');
    }
}
