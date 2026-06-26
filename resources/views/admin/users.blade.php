@extends('admin.layouts.app')

@section('title', 'Usuarios - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold">Usuarios</h2>
    </div>

    <form method="GET" action="{{ route('admin.users') }}"
          data-live-filter data-results="usersResults" data-loading="usersLoading"
          class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por nombre o email…"
               autocomplete="off"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm w-72 focus:outline-none focus:ring-2 focus:ring-brand/30">
        <select name="provider" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white">
            <option value="">Todos los proveedores</option>
            <option value="google" @selected($provider === 'google')>Google</option>
            <option value="microsoft" @selected($provider === 'microsoft')>Microsoft</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-dark transition">Buscar</button>
        <a href="{{ route('admin.users') }}" data-clear
           class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 {{ ($q !== '' || $provider) ? '' : 'hidden' }}">Limpiar</a>
        <span id="usersLoading" class="hidden text-xs text-gray-400">Actualizando…</span>
    </form>

    <div id="usersResults">
        @include('admin.partials.users-results')
    </div>
@endsection
