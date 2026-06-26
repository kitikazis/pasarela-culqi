@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <h2 class="text-xl font-bold mb-1">Resumen general</h2>
    <p class="text-sm text-gray-500 mb-6">Vista rápida de tu plataforma. Entra a cada sección para el detalle completo.</p>

    {{-- ===== Tarjetas de métricas ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.users') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Usuarios</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['users']) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i data-lucide="users" class="w-5 h-5"></i></div>
            </div>
            <div class="text-xs mt-3 {{ $stats['users_today'] > 0 ? 'text-green-600' : 'text-gray-400' }}">+{{ $stats['users_today'] }} hoy</div>
        </a>

        <a href="{{ route('admin.ads') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Anuncios</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['ads_total']) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-orange-50 text-brand flex items-center justify-center"><i data-lucide="megaphone" class="w-5 h-5"></i></div>
            </div>
            <div class="text-xs text-gray-500 mt-3 flex flex-wrap gap-x-3 gap-y-1">
                <span><span class="text-green-600">●</span> {{ $stats['ads_active'] }} act.</span>
                <span><span class="text-gray-400">●</span> {{ $stats['ads_inactive'] }} inact.</span>
                <span><span class="text-red-500">●</span> {{ $stats['ads_borrado'] }} borr.</span>
            </div>
        </a>

        <a href="{{ route('admin.transactions') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Ingresos</div>
                    <div class="text-3xl font-bold mt-1">S/ {{ number_format($stats['revenue'] / 100, 2) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            </div>
            <div class="text-xs text-gray-500 mt-3">{{ $stats['paid'] }} pagos confirmados</div>
        </a>

        <a href="{{ route('admin.transactions') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Transacciones</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['transactions']) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center"><i data-lucide="credit-card" class="w-5 h-5"></i></div>
            </div>
            <div class="text-xs mt-3 {{ $stats['pending'] > 0 ? 'text-yellow-600' : 'text-gray-400' }}">{{ $stats['pending'] }} pendientes</div>
        </a>
    </div>

    {{-- ===== Recientes (resumen, 5 de cada uno) ===== --}}
    <section class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Últimos usuarios</h3>
            <a href="{{ route('admin.users') }}" class="text-sm text-brand hover:underline">Ver todos →</a>
        </div>
        @include('admin.partials.users-table')
    </section>

    <section class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Últimos anuncios</h3>
            <a href="{{ route('admin.ads') }}" class="text-sm text-brand hover:underline">Ver todos →</a>
        </div>
        @include('admin.partials.ads-table')
    </section>

    <section>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Últimas transacciones</h3>
            <a href="{{ route('admin.transactions') }}" class="text-sm text-brand hover:underline">Ver todas →</a>
        </div>
        @include('admin.partials.transactions-table')
    </section>
@endsection
