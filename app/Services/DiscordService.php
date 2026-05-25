<?php

namespace App\Services;

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
        'construction'    => '🏗️ Construcción',
        'no_build'        => '⚡ Sin Construcción (Zero Build)',
        'ranked_build'    => '🏆 Ranked Construcción',
        'ranked_no_build' => '🏆 Ranked Sin Construcción',
    ];

    private const REGIONS = [
        'na-east' => '🌎 NA Este',
        'na-west' => '🌎 NA Oeste',
        'eu'      => '🌍 Europa',
        'br'      => '🌎 Brasil',
        'asia'    => '🌏 Asia',
        'oce'     => '🌏 Oceanía',
    ];

    private function headers(): array
    {
        return [
            'Authorization' => 'Bot ' . config('discord.token'),
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
        $appId   = config('discord.application_id');
        $guildId = config('discord.guild_id');

        $commands = [[
            'name'                       => 'announce',
            'description'                => 'Enviar un anuncio o partida privada de Fortnite',
            'default_member_permissions' => '8', // ADMINISTRATOR
        ]];

        $url = $guildId
            ? self::BASE . "/applications/{$appId}/guilds/{$guildId}/commands"
            : self::BASE . "/applications/{$appId}/commands";

        return Http::withHeaders($this->headers())
            ->put($url, $commands)
            ->json() ?? [];
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

    public function buildFortniteEmbed(
        string $mode,
        string $region,
        string $password,
        string $color
    ): array {
        return [
            'title'     => '🎮 Partida Privada de Fortnite',
            'color'     => self::color($color),
            'fields'    => [
                ['name' => '🕹️ Modo',      'value' => self::modeName($mode),     'inline' => true],
                ['name' => '🌐 Región',     'value' => self::regionName($region), 'inline' => true],
                ['name' => '🔑 Contraseña', 'value' => "||{$password}||",         'inline' => false],
            ],
            'timestamp' => now()->toIso8601String(),
            'footer'    => ['text' => 'Partida Privada • Fortnite'],
        ];
    }
}
