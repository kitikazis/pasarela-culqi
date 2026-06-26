@extends('admin.layouts.app')

@section('title', 'Transacciones - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold">Transacciones</h2>
    </div>

    <form method="GET" action="{{ route('admin.transactions') }}"
          data-live-filter data-results="txResults" data-loading="txLoading"
          class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por orden, cargo o cliente…"
               autocomplete="off"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm w-72 focus:outline-none focus:ring-2 focus:ring-brand/30">
        <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white">
            <option value="">Todos los estados</option>
            <option value="paid" @selected($status === 'paid')>Pagado</option>
            <option value="pending" @selected($status === 'pending')>Pendiente</option>
            <option value="failed" @selected($status === 'failed')>Fallido</option>
            <option value="refunded" @selected($status === 'refunded')>Devuelto</option>
        </select>
        <select name="method" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white">
            <option value="">Todos los métodos</option>
            <option value="card" @selected($method === 'card')>Tarjeta</option>
            <option value="yape" @selected($method === 'yape')>Yape</option>
            <option value="pagoefectivo" @selected($method === 'pagoefectivo')>PagoEfectivo</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-dark transition">Buscar</button>
        <a href="{{ route('admin.transactions') }}" data-clear
           class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 {{ ($q !== '' || $status || $method) ? '' : 'hidden' }}">Limpiar</a>
        <span id="txLoading" class="hidden text-xs text-gray-400">Actualizando…</span>
    </form>

    <div id="txResults">
        @include('admin.partials.transactions-results')
    </div>
@endsection
