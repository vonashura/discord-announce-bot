<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Pendiente - Discord Bot</title>
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

    <div class="w-full max-w-sm px-4 text-center">
        <div class="bg-gray-900 rounded-2xl p-8 border border-gray-800 shadow-xl">

            <div class="w-16 h-16 rounded-full bg-yellow-500/20 flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-xl font-bold text-white mb-2">Acceso pendiente</h1>
            <p class="text-gray-400 text-sm mb-6">
                Tu cuenta de Discord está registrada pero aún no ha sido aprobada por un administrador.<br>
                Contacta con el admin del servidor para que te dé acceso.
            </p>

            <form action="{{ route('auth.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full py-2.5 px-4 bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold rounded-xl transition-colors">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
