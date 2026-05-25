@extends('layouts.app')

@section('title', 'Configuración - Discord Bot')
@section('heading', 'Configuración del Bot')

@section('content')
<div class="max-w-2xl">

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

    {{-- Info boxes --}}
    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Endpoint Discord</p>
            <code class="text-xs text-indigo-400">/api/discord/interactions</code>
            <p class="text-xs text-gray-500 mt-1">Configura en Discord Developer Portal → Interactions Endpoint URL</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Registrar /announce</p>
            <code class="text-xs text-yellow-400">php artisan discord:register-commands</code>
            <p class="text-xs text-gray-500 mt-1">Ejecutar una vez después de guardar el token</p>
        </div>
    </div>

    <form action="{{ route('settings.save') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Group: Bot credentials --}}
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-700 bg-gray-800/50">
                <h3 class="text-sm font-semibold text-white">🔑 Credenciales del Bot</h3>
                <p class="text-xs text-gray-400">Obtenlas en <span class="text-indigo-400">discord.com/developers/applications</span></p>
            </div>
            <div class="p-5 space-y-4">
                @foreach(array_slice($fields, 0, 3, true) as $key => $meta)
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ $meta['label'] }}</label>
                    <input
                        type="{{ $meta['secret'] ? 'password' : 'text' }}"
                        name="{{ $key }}"
                        value="{{ $meta['secret'] ? '' : ($values[$key] ?? '') }}"
                        placeholder="{{ $values[$key] ? ($meta['secret'] ? '••••••••••••' : $values[$key]) : $meta['placeholder'] }}"
                        class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                    @if($meta['secret'] && $values[$key])
                    <p class="text-xs text-green-500 mt-1">✓ Configurado (déjalo vacío para no cambiar)</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Group: Server & channels --}}
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-700 bg-gray-800/50">
                <h3 class="text-sm font-semibold text-white">📡 Servidor y Canales</h3>
                <p class="text-xs text-gray-400">Activa modo desarrollador en Discord: Ajustes → Avanzado → Modo Desarrollador</p>
            </div>
            <div class="p-5 space-y-4">
                @foreach(array_slice($fields, 3, null, true) as $key => $meta)
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ $meta['label'] }}</label>
                    <input
                        type="text"
                        name="{{ $key }}"
                        value="{{ $values[$key] ?? '' }}"
                        placeholder="{{ $meta['placeholder'] }}"
                        class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit"
                class="flex-1 py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                💾 Guardar Configuración
            </button>

            @if(env('SETTINGS_PASSWORD'))
            <form action="{{ route('settings.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="py-3 px-4 bg-gray-700 hover:bg-gray-600 text-gray-300 font-medium rounded-xl transition-colors text-sm">
                    Cerrar sesión
                </button>
            </form>
            @endif
        </div>
    </form>

    {{-- Vercel setup guide --}}
    <div class="mt-8 bg-gray-900 rounded-2xl border border-gray-800 p-5">
        <h3 class="text-sm font-semibold text-white mb-3">🚀 Configuración en Vercel</h3>
        <p class="text-xs text-gray-400 mb-3">Variables que <strong class="text-white">debes</strong> configurar en el dashboard de Vercel (solo estas 3, todo lo demás aquí):</p>
        <div class="space-y-2">
            <div class="flex items-start gap-3 bg-gray-800 rounded-lg p-3">
                <code class="text-yellow-400 text-xs font-mono flex-shrink-0">APP_KEY</code>
                <p class="text-xs text-gray-400">Genera con: <code class="text-indigo-400">php artisan key:generate --show</code></p>
            </div>
            <div class="flex items-start gap-3 bg-gray-800 rounded-lg p-3">
                <code class="text-yellow-400 text-xs font-mono flex-shrink-0">SETTINGS_PASSWORD</code>
                <p class="text-xs text-gray-400">Contraseña para acceder a esta página de configuración</p>
            </div>
            <div class="flex items-start gap-3 bg-gray-800 rounded-lg p-3">
                <code class="text-yellow-400 text-xs font-mono flex-shrink-0">DATABASE_URL</code>
                <p class="text-xs text-gray-400">Connection string de Neon.tech (gratis) o Vercel Postgres</p>
            </div>
        </div>
    </div>
</div>
@endsection
