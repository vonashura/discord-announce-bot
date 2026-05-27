<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Discord Bot</title>
    @php
        try {
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile  = $manifest['resources/css/app.css']['file'] ?? null;
            $css = $cssFile ? file_get_contents(public_path('build/' . $cssFile)) : null;
        } catch (\Throwable $e) { $css = null; }
    @endphp
    @if($css)<style>{!! $css !!}</style>@endif
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-sm px-4">
        <div class="bg-gray-900 rounded-2xl p-8 border border-gray-800 text-center shadow-xl">

            {{-- Logo --}}
            <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center mx-auto mb-6">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-9 h-9 text-white">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057c.002.022.015.043.036.056a19.944 19.944 0 0 0 6.002 3.03.078.078 0 0 0 .084-.028c.463-.63.875-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-white mb-1">Announce Panel</h1>
            <p class="text-gray-400 text-sm mb-8">Inicia sesión con tu cuenta de Discord para acceder.</p>

            @if($errors->any())
            <div class="mb-6 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <a href="{{ route('auth.discord') }}"
               class="flex items-center justify-center gap-3 w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold rounded-xl transition-colors">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057c.002.022.015.043.036.056a19.944 19.944 0 0 0 6.002 3.03.078.078 0 0 0 .084-.028c.463-.63.875-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/>
                </svg>
                Iniciar sesión con Discord
            </a>

            <p class="text-xs text-gray-600 mt-6">Solo se solicita permiso para leer tu perfil básico.</p>
        </div>
    </div>

</body>
</html>
