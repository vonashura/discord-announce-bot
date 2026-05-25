<?php

namespace App\Http\Controllers;

use App\Services\DiscordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    public function __construct(private DiscordService $discord) {}

    // ──────────────────────────────────────────────────────────────────
    //  Entry point — Discord sends ALL interactions here
    // ──────────────────────────────────────────────────────────────────

    public function handle(Request $request): JsonResponse
    {
        return match ((int) $request->input('type')) {
            1 => response()->json(['type' => 1]),          // PING → PONG
            2 => $this->handleCommand($request),           // Slash command
            3 => $this->handleComponent($request),        // Select / Button
            5 => $this->handleModalSubmit($request),      // Modal submit
            default => response()->json(['error' => 'Unknown type'], 400),
        };
    }

    // ──────────────────────────────────────────────────────────────────
    //  /announce command
    // ──────────────────────────────────────────────────────────────────

    private function handleCommand(Request $request): JsonResponse
    {
        if ($request->input('data.name') !== 'announce') {
            return $this->ephemeral('Comando desconocido.');
        }

        return response()->json([
            'type' => 4,
            'data' => [
                'flags'      => 64, // ephemeral
                'content'    => '📢 **¿Qué tipo de anuncio quieres enviar?**',
                'components' => [[
                    'type'       => 1,
                    'components' => [[
                        'type'        => 3,
                        'custom_id'   => 'type_select',
                        'placeholder' => 'Selecciona el tipo de anuncio...',
                        'options'     => [
                            [
                                'label'       => 'Anuncio General',
                                'value'       => 'general',
                                'description' => 'Título y mensaje personalizado',
                                'emoji'       => ['name' => '📢'],
                            ],
                            [
                                'label'       => 'Partida Privada Fortnite',
                                'value'       => 'fortnite',
                                'description' => 'Modo, región y contraseña',
                                'emoji'       => ['name' => '🎮'],
                            ],
                        ],
                    ]],
                ]],
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    //  Component interactions (selects + buttons)
    // ──────────────────────────────────────────────────────────────────

    private function handleComponent(Request $request): JsonResponse
    {
        $id     = $request->input('data.custom_id');
        $values = $request->input('data.values', []);

        // ── Type select: General or Fortnite ──
        if ($id === 'type_select') {
            return ($values[0] ?? '') === 'fortnite'
                ? $this->showFortniteSelects()
                : $this->showGeneralColorSelect();
        }

        // ── General: color selected ──
        if ($id === 'general_color_select') {
            $color = $values[0] ?? 'azul';
            return $this->showGeneralColorSelect($color, true);
        }

        // ── General: write button → open modal ──
        if (str_starts_with($id, 'btn:general_write:')) {
            $color = explode(':', $id)[2] ?? 'azul';
            return response()->json([
                'type' => 9,
                'data' => [
                    'custom_id'  => "modal:general:{$color}",
                    'title'      => '📢 Nuevo Anuncio General',
                    'components' => [
                        $this->textInput('title',   'Título del Anuncio',  1, 'Ej: ¡Evento especial esta noche!', 256),
                        $this->textInput('message', 'Mensaje',              2, 'Escribe el contenido del anuncio...', 2000),
                    ],
                ],
            ]);
        }

        // ── Fortnite selects: state stored in button custom_id ──
        if (in_array($id, ['fortnite_mode_select', 'fortnite_region_select', 'fortnite_color_select'])) {
            // Read current state from button custom_id
            $btnId  = $this->findButtonId($request->input('message.components', []));
            $parts  = explode(':', $btnId ?: 'btn:fortnite_continue:construction:eu:azul');
            $mode   = $parts[2] ?? 'construction';
            $region = $parts[3] ?? 'eu';
            $color  = $parts[4] ?? 'azul';

            $val = $values[0] ?? '';
            match ($id) {
                'fortnite_mode_select'   => $mode   = $val,
                'fortnite_region_select' => $region = $val,
                'fortnite_color_select'  => $color  = $val,
            };

            return $this->showFortniteSelects($mode, $region, $color);
        }

        // ── Fortnite: continue button → open password modal ──
        if (str_starts_with($id, 'btn:fortnite_continue:')) {
            $parts  = explode(':', $id);
            $mode   = $parts[2] ?? 'construction';
            $region = $parts[3] ?? 'eu';
            $color  = $parts[4] ?? 'azul';

            return response()->json([
                'type' => 9,
                'data' => [
                    'custom_id'  => "modal:fortnite:{$mode}:{$region}:{$color}",
                    'title'      => '🎮 Partida Privada Fortnite',
                    'components' => [
                        $this->textInput('password', 'Contraseña de la Partida', 1, 'Ej: torneos2025', 50),
                    ],
                ],
            ]);
        }

        return response()->json(['type' => 6]); // deferred update
    }

    // ──────────────────────────────────────────────────────────────────
    //  Modal submits → send embed to Discord channel
    // ──────────────────────────────────────────────────────────────────

    private function handleModalSubmit(Request $request): JsonResponse
    {
        $id         = $request->input('data.custom_id');
        $components = $request->input('data.components', []);

        // ── General modal ──
        if (str_starts_with($id, 'modal:general:')) {
            $color   = explode(':', $id)[2] ?? 'azul';
            $title   = $this->modalValue($components, 'title');
            $message = $this->modalValue($components, 'message');

            $embed     = $this->discord->buildGeneralEmbed($title, $message, $color);
            $channelId = $this->discord->getAnnouncementChannelId();
            $roleId    = $this->discord->getAnnounceRoleId();
            $this->discord->sendEmbed($channelId, $embed, $roleId ? "<@&{$roleId}>" : null);

            return $this->updateWithSuccess('✅ **Anuncio enviado correctamente.**');
        }

        // ── Fortnite modal ──
        if (str_starts_with($id, 'modal:fortnite:')) {
            $parts    = explode(':', $id);
            $mode     = $parts[2] ?? 'construction';
            $region   = $parts[3] ?? 'eu';
            $color    = $parts[4] ?? 'azul';
            $password = $this->modalValue($components, 'password');

            $embed     = $this->discord->buildFortniteEmbed($mode, $region, $password, $color);
            $channelId = $this->discord->getFortniteChannelId();
            $roleId    = $this->discord->getFortniteRoleId();
            $this->discord->sendEmbed($channelId, $embed, $roleId ? "<@&{$roleId}>" : null);

            return $this->updateWithSuccess('✅ **Partida privada publicada.**');
        }

        return response()->json(['type' => 6]);
    }

    // ──────────────────────────────────────────────────────────────────
    //  Response builders
    // ──────────────────────────────────────────────────────────────────

    private function showGeneralColorSelect(string $selected = '', bool $enabled = false): JsonResponse
    {
        return response()->json([
            'type' => 7, // UPDATE_MESSAGE
            'data' => [
                'content'    => '🎨 **Elige el color del embed y pulsa el botón:**',
                'components' => [
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'general_color_select',
                            'placeholder' => 'Color del embed...',
                            'options'     => $this->colorOptions($selected),
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'      => 2,
                            'style'     => 3, // SUCCESS (verde)
                            'label'     => '✏️ Escribir Anuncio',
                            'custom_id' => 'btn:general_write:' . ($selected ?: 'azul'),
                            'disabled'  => !$enabled,
                        ]],
                    ],
                ],
            ],
        ]);
    }

    private function showFortniteSelects(
        string $mode   = 'construction',
        string $region = 'eu',
        string $color  = 'azul',
    ): JsonResponse {
        return response()->json([
            'type' => 7, // UPDATE_MESSAGE
            'data' => [
                'content'    => '🎮 **Configura la partida privada:**',
                'components' => [
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_mode_select',
                            'placeholder' => '🕹️ Modo de juego',
                            'options'     => [
                                ['label' => '🏗️ Construcción',             'value' => 'construction',    'default' => $mode === 'construction'],
                                ['label' => '⚡ Sin Construcción',          'value' => 'no_build',        'default' => $mode === 'no_build'],
                                ['label' => '🏆 Ranked Construcción',       'value' => 'ranked_build',    'default' => $mode === 'ranked_build'],
                                ['label' => '🏆 Ranked Sin Construcción',   'value' => 'ranked_no_build', 'default' => $mode === 'ranked_no_build'],
                            ],
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_region_select',
                            'placeholder' => '🌐 Región',
                            'options'     => [
                                ['label' => '🌎 NA Este',  'value' => 'na-east', 'default' => $region === 'na-east'],
                                ['label' => '🌎 NA Oeste', 'value' => 'na-west', 'default' => $region === 'na-west'],
                                ['label' => '🌍 Europa',   'value' => 'eu',      'default' => $region === 'eu'],
                                ['label' => '🌎 Brasil',   'value' => 'br',      'default' => $region === 'br'],
                                ['label' => '🌏 Asia',     'value' => 'asia',    'default' => $region === 'asia'],
                                ['label' => '🌏 Oceanía',  'value' => 'oce',     'default' => $region === 'oce'],
                            ],
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_color_select',
                            'placeholder' => '🎨 Color del embed',
                            'options'     => $this->colorOptions($color),
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'      => 2,
                            'style'     => 1, // PRIMARY (blurple)
                            'label'     => '🔑 Ingresar Contraseña',
                            'custom_id' => "btn:fortnite_continue:{$mode}:{$region}:{$color}",
                        ]],
                    ],
                ],
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────────

    private function colorOptions(string $selected = ''): array
    {
        $palette = [
            ['label' => '🔵 Azul',     'value' => 'azul'],
            ['label' => '🟢 Verde',    'value' => 'verde'],
            ['label' => '🔴 Rojo',     'value' => 'rojo'],
            ['label' => '🟡 Amarillo', 'value' => 'amarillo'],
            ['label' => '🟣 Morado',   'value' => 'morado'],
            ['label' => '🟠 Naranja',  'value' => 'naranja'],
            ['label' => '🩵 Cyan',     'value' => 'cyan'],
            ['label' => '⚫ Negro',    'value' => 'negro'],
        ];

        return array_map(fn($c) => [...$c, 'default' => $c['value'] === $selected], $palette);
    }

    private function textInput(string $id, string $label, int $style, string $placeholder, int $maxLength): array
    {
        return [
            'type'       => 1,
            'components' => [[
                'type'        => 4,
                'custom_id'   => $id,
                'label'       => $label,
                'style'       => $style, // 1=short, 2=paragraph
                'placeholder' => $placeholder,
                'required'    => true,
                'max_length'  => $maxLength,
            ]],
        ];
    }

    private function modalValue(array $components, string $id): string
    {
        foreach ($components as $row) {
            foreach ($row['components'] ?? [] as $c) {
                if ($c['custom_id'] === $id) {
                    return $c['value'] ?? '';
                }
            }
        }
        return '';
    }

    private function findButtonId(array $messageComponents): ?string
    {
        foreach ($messageComponents as $row) {
            foreach ($row['components'] ?? [] as $c) {
                if (($c['type'] ?? 0) === 2) {
                    return $c['custom_id'];
                }
            }
        }
        return null;
    }

    private function ephemeral(string $content): JsonResponse
    {
        return response()->json([
            'type' => 4,
            'data' => ['flags' => 64, 'content' => $content, 'components' => []],
        ]);
    }

    private function updateWithSuccess(string $content): JsonResponse
    {
        return response()->json([
            'type' => 7,
            'data' => ['content' => $content, 'components' => []],
        ]);
    }
}
