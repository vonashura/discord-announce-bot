<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class DiscordService
{
    private const BASE = 'https://discord.com/api/v10';

    private const COLORS = [
        'azul'     => 0x5865F2,
        'verde'    => 0x57F287,
        'rojo'     => 0xED4245,
        'amarillo' => 0xFEE75C,
        'morado'   => 0x9B59B6,
        'naranja'  => 0xE67E22,
        'cyan'     => 0x1ABC9C,
        'negro'    => 0x2C2F33,
    ];

    private const MODES = [
        'zero_build'    => 'Cero Construccion',
        'battle_royale' => 'Battle Royale',
        'reload_build'  => 'Recarga (Construccion)',
        'reload_zero'   => 'Recarga (Cero Build)',
    ];

    private const MODALIDADES = [
        'solo'  => 'Solitario',
        'duo'   => 'Duo',
        'trio'  => 'Trio',
        'squad' => 'Escuadron',
    ];

    private const REGIONS = [
        'eu'      => 'Europa',
        'na-east' => 'NA Este',
        'na-west' => 'NA Oeste',
        'br'      => 'Brasil',
        'asia'    => 'Asia',
        'oce'     => 'Oceania',
    ];

    // ── Config resolution: DB → env fallback ───────────────────────
    private function cfg(string $key): ?string
    {
        $dbKey = 'discord_' . $key;
        try {
            $val = Setting::get($dbKey);
            if ($val !== null && $val !== '') {
                return $val;
            }
        } catch (\Exception) {
            // DB not ready — fall through to env
        }
        return config("discord.{$key}") ?: null;
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bot ' . $this->cfg('token'),
            'Content-Type'  => 'application/json',
        ];
    }

    public function sendEmbed(string $channelId, array $embed, ?string $content = null): array
    {
        $payload = ['embeds' => [$embed]];
        if ($content) {
            $payload['content'] = $content;
        }

        return Http::withHeaders($this->headers())
            ->post(self::BASE . "/channels/{$channelId}/messages", $payload)
            ->json() ?? [];
    }

    public function sendWebhook(string $webhookUrl, array $embed, ?string $content = null): array
    {
        $payload = ['embeds' => [$embed]];
        if ($content) {
            $payload['content'] = $content;
        }

        return Http::post($webhookUrl, $payload)->json() ?? [];
    }

    public function registerCommands(): array
    {
        $appId   = $this->cfg('application_id');
        $guildId = $this->cfg('guild_id');

        $commands = [[
            'name'                       => 'announce',
            'description'                => 'Enviar un anuncio o partida privada de Fortnite',
            'default_member_permissions' => '8',
        ]];

        $url = $guildId
            ? self::BASE . "/applications/{$appId}/guilds/{$guildId}/commands"
            : self::BASE . "/applications/{$appId}/commands";

        return Http::withHeaders($this->headers())
            ->put($url, $commands)
            ->json() ?? [];
    }

    public function getPublicKey(): ?string
    {
        return $this->cfg('public_key');
    }

    public function getAnnouncementChannelId(): ?string
    {
        return $this->cfg('announcement_channel_id');
    }

    public function getFortniteChannelId(): ?string
    {
        return $this->cfg('fortnite_channel_id') ?: $this->cfg('announcement_channel_id');
    }

    public function getAnnounceRoleId(): ?string
    {
        return $this->cfg('announce_role_id') ?: null;
    }

    public function getFortniteRoleId(): ?string
    {
        return $this->cfg('fortnite_role_id') ?: null;
    }

    public static function color(string $key): int
    {
        return self::COLORS[$key] ?? 0x5865F2;
    }

    public static function modeName(string $key): string
    {
        return self::MODES[$key] ?? $key;
    }

    public static function regionName(string $key): string
    {
        return self::REGIONS[$key] ?? $key;
    }

    public function buildGeneralEmbed(string $title, string $message, string $color): array
    {
        return [
            'title'       => $title,
            'description' => $message,
            'color'       => self::color($color),
            'timestamp'   => now()->toIso8601String(),
            'footer'      => ['text' => '📢 Anuncio General'],
        ];
    }

    public static function modalidadName(string $key): string
    {
        return self::MODALIDADES[$key] ?? $key;
    }

    public function buildFortniteEmbed(string $mode, string $modalidad, string $clasificatoria, string $region, string $password, string $color): array
    {
        return [
            'title'     => 'Partida Privada de Fortnite',
            'color'     => self::color($color),
            'fields'    => [
                ['name' => 'Modo',          'value' => self::modeName($mode),          'inline' => true],
                ['name' => 'Modalidad',     'value' => self::modalidadName($modalidad), 'inline' => true],
                ['name' => 'Clasificatoria','value' => $clasificatoria === 'si' ? 'Si' : 'No', 'inline' => true],
                ['name' => 'Region',        'value' => self::regionName($region),       'inline' => true],
                ['name' => 'Contrasena',    'value' => "||{$password}||",               'inline' => false],
            ],
            'timestamp' => now()->toIso8601String(),
            'footer'    => ['text' => 'Partida Privada | Fortnite'],
        ];
    }
}
