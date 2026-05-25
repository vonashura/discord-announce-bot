<?php

namespace App\Http\Controllers;

use App\Services\DiscordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    public function __construct(private DiscordService $discord) {}

    public function handle(Request $request): JsonResponse
    {
        return match ((int) $request->input('type')) {
            1 => response()->json(['type' => 1]),
            2 => $this->handleCommand($request),
            3 => $this->handleComponent($request),
            5 => $this->handleModalSubmit($request),
            default => response()->json(['error' => 'Unknown type'], 400),
        };
    }

    private function handleCommand(Request $request): JsonResponse
    {
        if ($request->input('data.name') !== 'announce') {
            return $this->ephemeral('Comando desconocido.');
        }

        return response()->json([
            'type' => 4,
            'data' => [
                'flags'      => 64,
                'content'    => '**¿Qué tipo de anuncio quieres enviar?**',
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
                            ],
                            [
                                'label'       => 'Partida Privada Fortnite',
                                'value'       => 'fortnite',
                                'description' => 'Modo, modalidad, región y contraseña',
                            ],
                        ],
                    ]],
                ]],
            ],
        ]);
    }

    private function handleComponent(Request $request): JsonResponse
    {
        $id     = $request->input('data.custom_id');
        $values = $request->input('data.values', []);

        if ($id === 'type_select') {
            return ($values[0] ?? '') === 'fortnite'
                ? $this->showFortniteSelects()
                : $this->showGeneralColorSelect();
        }

        if ($id === 'general_color_select') {
            return $this->showGeneralColorSelect($values[0] ?? 'azul', true);
        }

        if (str_starts_with($id, 'btn:general_write:')) {
            $color = explode(':', $id)[2] ?? 'azul';
            return response()->json([
                'type' => 9,
                'data' => [
                    'custom_id'  => "modal:general:{$color}",
                    'title'      => 'Nuevo Anuncio General',
                    'components' => [
                        $this->textInput('title',   'Título del Anuncio', 1, 'Ej: ¡Evento especial esta noche!', 256),
                        $this->textInput('message', 'Mensaje',            2, 'Escribe el contenido del anuncio...', 2000),
                    ],
                ],
            ]);
        }

        // Fortnite selects: read current state from button custom_id embedded in the message
        if (in_array($id, ['fortnite_mode_select', 'fortnite_modalidad_select', 'fortnite_clasificatoria_select', 'fortnite_region_select'])) {
            $btnId = $this->findButtonId($request->input('message.components', []));
            $parts = explode(':', $btnId ?: 'btn:fortnite_continue:zero_build:solo:no:eu');
            $mode           = $parts[2] ?? 'zero_build';
            $modalidad      = $parts[3] ?? 'solo';
            $clasificatoria = $parts[4] ?? 'no';
            $region         = $parts[5] ?? 'eu';

            $val = $values[0] ?? '';
            match ($id) {
                'fortnite_mode_select'           => $mode           = $val,
                'fortnite_modalidad_select'      => $modalidad      = $val,
                'fortnite_clasificatoria_select' => $clasificatoria = $val,
                'fortnite_region_select'         => $region         = $val,
            };

            return $this->showFortniteSelects($mode, $modalidad, $clasificatoria, $region);
        }

        if (str_starts_with($id, 'btn:fortnite_continue:')) {
            $parts          = explode(':', $id);
            $mode           = $parts[2] ?? 'zero_build';
            $modalidad      = $parts[3] ?? 'solo';
            $clasificatoria = $parts[4] ?? 'no';
            $region         = $parts[5] ?? 'eu';

            return response()->json([
                'type' => 9,
                'data' => [
                    'custom_id'  => "modal:fortnite:{$mode}:{$modalidad}:{$clasificatoria}:{$region}",
                    'title'      => 'Partida Privada Fortnite',
                    'components' => [
                        $this->textInput('password', 'Contraseña de la Partida', 1, 'Ej: torneos2025', 50),
                    ],
                ],
            ]);
        }

        return response()->json(['type' => 6]);
    }

    private function handleModalSubmit(Request $request): JsonResponse
    {
        $id         = $request->input('data.custom_id');
        $components = $request->input('data.components', []);

        if (str_starts_with($id, 'modal:general:')) {
            $color   = explode(':', $id)[2] ?? 'azul';
            $title   = $this->modalValue($components, 'title');
            $message = $this->modalValue($components, 'message');

            $embed     = $this->discord->buildGeneralEmbed($title, $message, $color);
            $channelId = $this->discord->getAnnouncementChannelId();
            $roleId    = $this->discord->getAnnounceRoleId();
            $this->discord->sendEmbed($channelId, $embed, $roleId ? "<@&{$roleId}>" : null);

            return $this->updateWithSuccess('**Anuncio enviado correctamente.**');
        }

        if (str_starts_with($id, 'modal:fortnite:')) {
            $parts          = explode(':', $id);
            $mode           = $parts[2] ?? 'zero_build';
            $modalidad      = $parts[3] ?? 'solo';
            $clasificatoria = $parts[4] ?? 'no';
            $region         = $parts[5] ?? 'eu';
            $password       = $this->modalValue($components, 'password');

            $embed     = $this->discord->buildFortniteEmbed($mode, $modalidad, $clasificatoria, $region, $password, 'azul');
            $channelId = $this->discord->getFortniteChannelId();
            $roleId    = $this->discord->getFortniteRoleId();
            $this->discord->sendEmbed($channelId, $embed, $roleId ? "<@&{$roleId}>" : null);

            return $this->updateWithSuccess('**Partida privada publicada.**');
        }

        return response()->json(['type' => 6]);
    }

    private function showGeneralColorSelect(string $selected = '', bool $enabled = false): JsonResponse
    {
        return response()->json([
            'type' => 7,
            'data' => [
                'content'    => '**Elige el color del embed y pulsa el botón:**',
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
                            'style'     => 3,
                            'label'     => 'Escribir Anuncio',
                            'custom_id' => 'btn:general_write:' . ($selected ?: 'azul'),
                            'disabled'  => !$enabled,
                        ]],
                    ],
                ],
            ],
        ]);
    }

    private function showFortniteSelects(
        string $mode           = 'zero_build',
        string $modalidad      = 'solo',
        string $clasificatoria = 'no',
        string $region         = 'eu',
    ): JsonResponse {
        return response()->json([
            'type' => 7,
            'data' => [
                'content'    => '**Configura la partida privada:**',
                'components' => [
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_mode_select',
                            'placeholder' => 'Modo de juego',
                            'options'     => [
                                ['label' => 'Cero Construccion',      'value' => 'zero_build',    'default' => $mode === 'zero_build'],
                                ['label' => 'Battle Royale',          'value' => 'battle_royale', 'default' => $mode === 'battle_royale'],
                                ['label' => 'Recarga (Construccion)', 'value' => 'reload_build',  'default' => $mode === 'reload_build'],
                                ['label' => 'Recarga (Cero Build)',   'value' => 'reload_zero',   'default' => $mode === 'reload_zero'],
                            ],
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_modalidad_select',
                            'placeholder' => 'Modalidad',
                            'options'     => [
                                ['label' => 'Solitario',  'value' => 'solo',  'default' => $modalidad === 'solo'],
                                ['label' => 'Duo',        'value' => 'duo',   'default' => $modalidad === 'duo'],
                                ['label' => 'Trio',       'value' => 'trio',  'default' => $modalidad === 'trio'],
                                ['label' => 'Escuadron',  'value' => 'squad', 'default' => $modalidad === 'squad'],
                            ],
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_clasificatoria_select',
                            'placeholder' => 'Clasificatoria',
                            'options'     => [
                                ['label' => 'No', 'value' => 'no', 'default' => $clasificatoria === 'no'],
                                ['label' => 'Si', 'value' => 'si', 'default' => $clasificatoria === 'si'],
                            ],
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'        => 3,
                            'custom_id'   => 'fortnite_region_select',
                            'placeholder' => 'Region',
                            'options'     => [
                                ['label' => 'Europa',   'value' => 'eu',      'default' => $region === 'eu'],
                                ['label' => 'NA Este',  'value' => 'na-east', 'default' => $region === 'na-east'],
                                ['label' => 'NA Oeste', 'value' => 'na-west', 'default' => $region === 'na-west'],
                                ['label' => 'Brasil',   'value' => 'br',      'default' => $region === 'br'],
                                ['label' => 'Asia',     'value' => 'asia',    'default' => $region === 'asia'],
                                ['label' => 'Oceania',  'value' => 'oce',     'default' => $region === 'oce'],
                            ],
                        ]],
                    ],
                    [
                        'type'       => 1,
                        'components' => [[
                            'type'      => 2,
                            'style'     => 1,
                            'label'     => 'Ingresar Contrasena',
                            'custom_id' => "btn:fortnite_continue:{$mode}:{$modalidad}:{$clasificatoria}:{$region}",
                        ]],
                    ],
                ],
            ],
        ]);
    }

    private function colorOptions(string $selected = ''): array
    {
        $palette = [
            ['label' => 'Azul',     'value' => 'azul'],
            ['label' => 'Verde',    'value' => 'verde'],
            ['label' => 'Rojo',     'value' => 'rojo'],
            ['label' => 'Amarillo', 'value' => 'amarillo'],
            ['label' => 'Morado',   'value' => 'morado'],
            ['label' => 'Naranja',  'value' => 'naranja'],
            ['label' => 'Cyan',     'value' => 'cyan'],
            ['label' => 'Negro',    'value' => 'negro'],
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
                'style'       => $style,
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
