@extends('admin.layouts.app')

@section('title', 'Usuarios - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold">Usuarios</h2>
        <p class="text-sm text-gray-500">{{ number_format($users->total()) }} resultado(s)</p>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o email…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm w-72 focus:outline-none focus:ring-2 focus:ring-brand/30">
        <select name="provider" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white">
            <option value="">Todos los proveedores</option>
            <option value="google" @selected($provider === 'google')>Google</option>
            <option value="microsoft" @selected($provider === 'microsoft')>Microsoft</option>
        </select>
        <button class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-dark transition">Buscar</button>
        @if($q !== '' || $provider)
            <a href="{{ route('admin.users') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
        @endif
    </form>

    @include('admin.partials.users-table')

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
