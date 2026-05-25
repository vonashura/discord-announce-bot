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
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Anuncio General
            </button>
            <button type="button" onclick="switchTab('fortnite')" id="tab-fortnite"
                class="flex-1 py-2.5 px-4 rounded-lg text-sm font-semibold transition-all text-gray-400 hover:text-white flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
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

                <div id="general-webhook-field" class="hidden">
                    <label class="block text-sm font-medium text-gray-300 mb-2">URL del Webhook</label>
                    <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Título</label>
                    <input type="text" name="title" id="g-title" required maxlength="256"
                        placeholder="Título del anuncio..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Mensaje</label>
                    <textarea name="message" id="g-message" required maxlength="2000" rows="5"
                        placeholder="Escribe el contenido del anuncio..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-3">Color del embed</label>
                    <div class="grid grid-cols-4 gap-2" id="color-general"></div>
                    <input type="hidden" name="color" id="g-color" value="azul">
                </div>

                <button type="submit"
                    class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar Anuncio
                </button>
            </form>
        </div>

        {{-- ── FORTNITE FORM ── --}}
        <div id="form-fortnite" class="hidden">
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

                <div id="fortnite-webhook-field" class="hidden">
                    <label class="block text-sm font-medium text-gray-300 mb-2">URL del Webhook</label>
                    <input type="url" name="webhook_url" placeholder="https://discord.com/api/webhooks/..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Modo de juego</label>
                    <select name="mode" id="f-mode"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="construction">Construccion</option>
                        <option value="no_build">Sin Construccion (Zero Build)</option>
                        <option value="ranked_build">Ranked Construccion</option>
                        <option value="ranked_no_build">Ranked Sin Construccion</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Region</label>
                    <select name="region" id="f-region"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="na-east">NA Este</option>
                        <option value="na-west">NA Oeste</option>
                        <option value="eu" selected>Europa</option>
                        <option value="br">Brasil</option>
                        <option value="asia">Asia</option>
                        <option value="oce">Oceania</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Contrasena</label>
                    <input type="text" name="password" id="f-password" required maxlength="50"
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
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
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
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
                            <div id="preview-fields" class="space-y-2 pt-1 hidden">
                                <div class="flex gap-4">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400">Modo</p>
                                        <p id="pf-mode" class="text-xs text-white">Construccion</p>
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
