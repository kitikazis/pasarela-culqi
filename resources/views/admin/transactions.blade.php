@extends('admin.layouts.app')

@section('title', 'Transacciones - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-4">
        <h2 class="text-xl font-bold">Transacciones</h2>
        <p class="text-sm text-gray-500">{{ number_format($transactions->total()) }} resultado(s)</p>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por orden, cargo o cliente…"
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
        <button class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-dark transition">Buscar</button>
        @if($q !== '' || $status || $method)
            <a href="{{ route('admin.transactions') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
        @endif
    </form>

    @include('admin.partials.transactions-table')

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
@endsection
