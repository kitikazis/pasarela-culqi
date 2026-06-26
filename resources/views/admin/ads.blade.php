@extends('admin.layouts.app')

@section('title', 'Anuncios - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold">Anuncios</h2>
        <p class="text-sm text-gray-500">{{ number_format($ads->total()) }} resultado(s)</p>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar en la descripción…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-brand/30">
        <select name="estado" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white">
            <option value="">Todos los estados</option>
            <option value="active" @selected($estado === 'active')>Activos</option>
            <option value="inactive" @selected($estado === 'inactive')>Inactivos</option>
            <option value="borrado" @selected($estado === 'borrado')>Borrados</option>
        </select>
        <select name="categoria" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white">
            <option value="">Todas las categorías</option>
            <option value="venta" @selected($categoria === 'venta')>Venta</option>
            <option value="compra" @selected($categoria === 'compra')>Compra</option>
            <option value="trabajo" @selected($categoria === 'trabajo')>Trabajo</option>
            <option value="busca" @selected($categoria === 'busca')>Busca</option>
        </select>
        <button class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-dark transition">Buscar</button>
        @if($q !== '' || $estado || $categoria)
            <a href="{{ route('admin.ads') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
        @endif
    </form>

    @include('admin.partials.ads-table')

    <div class="mt-4">
        {{ $ads->links() }}
    </div>
@endsection
