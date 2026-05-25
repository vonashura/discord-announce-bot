<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Discord Announce Bot')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex">

    {{-- ── Sidebar ── --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col fixed inset-y-0 left-0">
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-lg font-bold">🤖</div>
                <div>
                    <p class="font-bold text-white text-sm leading-tight">Discord Bot</p>
                    <p class="text-xs text-gray-400">Announce Panel</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('dashboard') ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <span>📤</span> Enviar Anuncio
            </a>
            <a href="{{ route('settings') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('settings*') ? 'bg-indigo-600/20 text-indigo-400' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                <span>⚙️</span> Configuración
            </a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <p class="text-xs text-gray-500">
                Endpoint Discord:<br>
                <code class="text-indigo-400 text-xs">/api/discord/interactions</code>
            </p>
        </div>
    </aside>

    {{-- ── Main content ── --}}
    <div class="ml-64 flex-1 flex flex-col min-h-screen">
        <header class="bg-gray-900 border-b border-gray-800 px-8 py-4">
            <h1 class="text-xl font-bold text-white">@yield('heading', 'Panel de Anuncios')</h1>
        </header>

        <main class="flex-1 p-8">
            @yield('content')
        </main>
    </div>

</body>
</html>
