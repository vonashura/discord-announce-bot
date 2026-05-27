@extends('layouts.app')

@section('title', 'Gestión de Usuarios')
@section('heading', 'Gestión de Usuarios')

@section('content')
<div class="max-w-3xl">

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
        <div class="p-5 border-b border-gray-800">
            <p class="text-sm text-gray-400">
                {{ $users->count() }} {{ $users->count() === 1 ? 'usuario registrado' : 'usuarios registrados' }}
                · {{ $users->where('approved', true)->count() }} aprobados
                · {{ $users->where('approved', false)->count() }} pendientes
            </p>
        </div>

        @forelse($users as $user)
        <div class="flex items-center gap-4 px-5 py-4 {{ !$loop->last ? 'border-b border-gray-800' : '' }}">
            {{-- Avatar --}}
            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->username }}"
                 class="w-10 h-10 rounded-full bg-gray-700 flex-shrink-0">

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-white text-sm leading-tight">{{ $user->username }}</p>
                <p class="text-xs text-gray-500 font-mono">{{ $user->discord_id }}</p>
            </div>

            {{-- Badge --}}
            <div class="flex-shrink-0">
                @if($user->is_admin)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-400">Admin</span>
                @elseif($user->approved)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400">Aprobado</span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">Pendiente</span>
                @endif
            </div>

            {{-- Actions --}}
            @if(!$user->is_admin)
            <div class="flex-shrink-0">
                @if(!$user->approved)
                <form action="{{ route('admin.approve', $user->discord_id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        Aprobar
                    </button>
                </form>
                @else
                <form action="{{ route('admin.revoke', $user->discord_id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 bg-red-600/80 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors">
                        Revocar
                    </button>
                </form>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="px-5 py-10 text-center text-gray-500 text-sm">
            Ningún usuario se ha conectado todavía.
        </div>
        @endforelse
    </div>
</div>
@endsection
