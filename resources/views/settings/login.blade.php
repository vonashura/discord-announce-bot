@extends('layouts.app')

@section('title', 'Acceso - Configuracion')
@section('heading', 'Configuracion del Bot')

@section('content')
<div class="max-w-sm mx-auto mt-16">
    <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-gray-300" style="font-size:32px">lock</span>
        </div>
        <h2 class="text-xl font-bold text-white mb-2">Acceso restringido</h2>
        <p class="text-gray-400 text-sm mb-6">Introduce la contrasena para acceder a la configuracion del bot.</p>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('settings.login') }}" method="POST">
            @csrf
            <input type="password" name="password" autofocus required
                placeholder="Contrasena..."
                class="w-full bg-gray-700 border border-gray-600 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-4">
            <button type="submit"
                class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-colors">
                Entrar
            </button>
        </form>
    </div>
</div>
@endsection
