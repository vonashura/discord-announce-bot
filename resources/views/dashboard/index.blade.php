@extends('layouts.app')

@section('title', 'Enviar Anuncio')
@section('heading', 'Enviar Anuncio')

@section('content')
<div class="flex gap-8 items-start">

    {{-- ── LEFT: Form ── --}}
    <div class="flex-1 max-w-xl">

        @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm space-y-1">
            @foreach($errors->all() as $error)
            <p>• {{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Tabs --}}
        <div class="flex gap-1 mb-6 bg-gray-900 p-1 rounded-xl border border-gray-800">
            <button type="button" onclick="switchTab('general')" id="tab-general"
                class="flex-1 py-2.5 px-4 rounded-lg text-sm font-semibold transition-all bg-indigo-600 text-white flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">campaign</span>
                Anuncio General
            </button>
            <button type="button" onclick="switchTab('fortnite')" id="tab-fortnite"
                class="flex-1 py-2.5 px-4 rounded-lg text-sm font-semibold transition-all text-gray-400 hover:text-white flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">sports_esports</span>
                Partida Fortnite
            </button>
        </div>

        {{-- ── GENERAL FORM ── --}}
        <div id="form-general">
            <form action="{{ route('send') }}" method="POST" class="space-y-5" onchange="updatePreview()" oninput="updatePreview()">
                @csrf
                <input type="hidden" name="type" value="general">

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Destino</label>
                    <select name="channel" onchange="toggleWebhookField('general', this.value)"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
                        <option value="announcement">Canal de Anuncios</option>
                        <option value="webhook">Webhook personalizado</option>
                    </select>
                </div>

                <div id="general-webhook-field">
                    <label class="block text-sm font-medium text-gray-300 mb-2">URL del Webhook</label>
                    <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Titulo</label>
                    <input type="text" name="title" id="g-title" required maxlength="256"
                        placeholder="Titulo del anuncio..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Mensaje</label>
                    {{-- Formatting toolbar --}}
                    <div class="flex flex-wrap items-center gap-1 mb-1 px-2 py-1.5 bg-gray-900 rounded-t-xl border border-gray-700 border-b-0">
                        <button type="button" onclick="insertFormatting('g-message','bold')" title="Negrita (**texto**)" class="fmt-btn font-bold">B</button>
                        <button type="button" onclick="insertFormatting('g-message','italic')" title="Cursiva (*texto*)" class="fmt-btn italic">I</button>
                        <button type="button" onclick="insertFormatting('g-message','underline')" title="Subrayado (__texto__)" class="fmt-btn underline">U</button>
                        <button type="button" onclick="insertFormatting('g-message','strike')" title="Tachado (~~texto~~)" class="fmt-btn line-through">S</button>
                        <span class="w-px h-4 bg-gray-700 mx-0.5"></span>
                        <button type="button" onclick="insertFormatting('g-message','code')" title="Código inline" class="fmt-btn font-mono text-xs">`c`</button>
                        <button type="button" onclick="insertFormatting('g-message','codeblock')" title="Bloque de código" class="fmt-btn font-mono text-xs">```</button>
                        <button type="button" onclick="insertFormatting('g-message','spoiler')" title="Spoiler (||texto||)" class="fmt-btn font-mono text-xs">||</button>
                        <span class="w-px h-4 bg-gray-700 mx-0.5"></span>
                        <button type="button" onclick="insertFormatting('g-message','list')" title="Lista (- elemento)" class="fmt-btn text-base leading-none">≡</button>
                        <button type="button" onclick="insertFormatting('g-message','quote')" title="Cita (> texto)" class="fmt-btn">&gt;</button>
                        <span class="w-px h-4 bg-gray-700 mx-0.5"></span>
                        <button type="button" onclick="insertFormatting('g-message','everyone')" title="Mencionar a todos" class="fmt-btn text-indigo-400 font-semibold">@everyone</button>
                        <button type="button" onclick="insertFormatting('g-message','here')" title="Mencionar activos" class="fmt-btn text-indigo-400 font-semibold">@here</button>
                    </div>
                    <textarea name="message" id="g-message" required maxlength="2000" rows="5"
                        placeholder="Escribe el contenido del anuncio..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-b-xl rounded-t-none px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                    <p class="text-xs text-gray-600 mt-1">Selecciona texto y pulsa un botón para aplicar formato. Enter = nueva línea.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Color del embed</label>
                    <div class="grid grid-cols-4 gap-2" id="color-general"></div>
                    <input type="hidden" name="color" id="g-color" value="azul">
                </div>

                <button type="submit"
                    class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">send</span>
                    Enviar Anuncio
                </button>
            </form>
        </div>

        {{-- ── FORTNITE FORM ── --}}
        <div id="form-fortnite">
            <form action="{{ route('send') }}" method="POST" class="space-y-5" onchange="updatePreview()" oninput="updatePreview()">
                @csrf
                <input type="hidden" name="type" value="fortnite">

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Destino</label>
                    <select name="channel" onchange="toggleWebhookField('fortnite', this.value)"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors">
                        <option value="fortnite">Canal de Fortnite</option>
                        <option value="announcement">Canal de Anuncios</option>
                        <option value="webhook">Webhook personalizado</option>
                    </select>
                </div>

                <div id="fortnite-webhook-field">
                    <label class="block text-sm font-medium text-gray-300 mb-2">URL del Webhook</label>
                    <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Modo de juego</label>
                    <select name="mode" id="f-mode" onchange="window.updatePreview()"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="zero_build">Cero Construccion</option>
                        <option value="battle_royale">Battle Royale</option>
                        <option value="reload_build">Recarga (Construccion)</option>
                        <option value="reload_zero">Recarga (Cero Build)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Modalidad</label>
                    <select name="modalidad" id="f-modalidad" onchange="window.updatePreview()"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="solo">Solitario</option>
                        <option value="duo">Duo</option>
                        <option value="trio">Trio</option>
                        <option value="squad">Escuadron</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Clasificatoria</label>
                    <select name="clasificatoria" id="f-clasificatoria" onchange="window.updatePreview()"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="no">No</option>
                        <option value="si">Si</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Region</label>
                    <select name="region" id="f-region" onchange="window.updatePreview()"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="eu" selected>Europa</option>
                        <option value="na-east">NA Este</option>
                        <option value="na-west">NA Oeste</option>
                        <option value="br">Brasil</option>
                        <option value="asia">Asia</option>
                        <option value="oce">Oceania</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Contrasena</label>
                    <input type="password" name="password" id="f-password" required maxlength="50"
                        autocomplete="off"
                        placeholder="Contrasena de la partida..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Color del embed</label>
                    <div class="grid grid-cols-4 gap-2" id="color-fortnite"></div>
                    <input type="hidden" name="color" id="f-color" value="azul">
                </div>

                <button type="submit"
                    class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">sports_esports</span>
                    Publicar Partida
                </button>
            </form>
        </div>
    </div>

    {{-- ── RIGHT: Live Preview ── --}}
    <div class="w-80 flex-shrink-0 sticky top-8">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Vista previa</p>

        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-white">smart_toy</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="font-semibold text-white text-sm">Bot</span>
                        <span class="text-xs text-gray-500">Hoy</span>
                    </div>
                    <div id="embed-preview" class="rounded-r-md overflow-hidden" style="border-left: 4px solid #5865F2;">
                        <div class="bg-gray-700/80 p-3 space-y-2">
                            <div id="preview-title" class="font-semibold text-white text-sm">Titulo del anuncio</div>
                            <div id="preview-body" class="text-gray-300 text-xs leading-relaxed">Tu mensaje aqui...</div>
                            <div id="preview-fields" class="space-y-1 pt-1">
                                <div class="flex gap-3 flex-wrap">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400">Modo</p>
                                        <p id="pf-mode" class="text-xs text-white">Cero Construccion</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400">Modalidad</p>
                                        <p id="pf-modalidad" class="text-xs text-white">Solitario</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400">Clasificatoria</p>
                                        <p id="pf-clasificatoria" class="text-xs text-white">No</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400">Region</p>
                                        <p id="pf-region" class="text-xs text-white">Europa</p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-400">Contrasena</p>
                                    <p class="text-xs text-gray-500 italic">||oculta||</p>
                                </div>
                            </div>
                            <div class="pt-1">
                                <p class="text-xs text-gray-500" id="preview-footer">Anuncio General</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-gray-900 rounded-xl border border-gray-800 space-y-1">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Endpoint Discord</p>
            <code class="text-xs text-indigo-400 break-all">/api/discord/interactions</code>
        </div>
    </div>

</div>
@endsection
