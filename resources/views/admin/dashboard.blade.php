@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    {{-- ===== Tarjetas de métricas ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        {{-- Usuarios --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Usuarios</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['users']) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">👥</div>
            </div>
            <div class="text-xs mt-3 {{ $stats['users_today'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                +{{ $stats['users_today'] }} hoy
            </div>
        </div>

        {{-- Anuncios --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Anuncios</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['ads_total']) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-orange-50 text-brand flex items-center justify-center text-xl">📢</div>
            </div>
            <div class="text-xs text-gray-500 mt-3 flex flex-wrap gap-x-3 gap-y-1">
                <span><span class="text-green-600">●</span> {{ $stats['ads_active'] }} activos</span>
                <span><span class="text-gray-400">●</span> {{ $stats['ads_inactive'] }} inactivos</span>
                <span><span class="text-red-500">●</span> {{ $stats['ads_borrado'] }} borrados</span>
            </div>
        </div>

        {{-- Ingresos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Ingresos</div>
                    <div class="text-3xl font-bold mt-1">S/ {{ number_format($stats['revenue'] / 100, 2) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl">💰</div>
            </div>
            <div class="text-xs text-gray-500 mt-3">{{ $stats['paid'] }} pagos confirmados</div>
        </div>

        {{-- Transacciones --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-400">Transacciones</div>
                    <div class="text-3xl font-bold mt-1">{{ number_format($stats['transactions']) }}</div>
                </div>
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">💳</div>
            </div>
            <div class="text-xs mt-3 {{ $stats['pending'] > 0 ? 'text-yellow-600' : 'text-gray-400' }}">
                {{ $stats['pending'] }} pendientes
            </div>
        </div>
    </div>

    {{-- ===== Usuarios ===== --}}
    <section id="users" class="mb-8 scroll-mt-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Usuarios recientes</h2>
            <span class="text-sm text-gray-400">Últimos {{ count($users) }} de {{ number_format($stats['users']) }}</span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Usuario</th>
                        <th class="px-4 py-3 text-left font-medium">Proveedor</th>
                        <th class="px-4 py-3 text-center font-medium">Créditos</th>
                        <th class="px-4 py-3 text-center font-medium">Anuncios</th>
                        <th class="px-4 py-3 text-left font-medium">Registrado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($users as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($u->avatar)
                                    <img src="{{ $u->avatar }}" alt="" referrerpolicy="no-referrer"
                                         class="w-9 h-9 rounded-full object-cover bg-gray-100">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-brand/10 text-brand flex items-center justify-center font-semibold">
                                        {{ strtoupper(mb_substr($u->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-800 truncate">{{ $u->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 capitalize text-gray-600">{{ $u->provider ?? '—' }}</td>
                        <td class="px-4 py-3 text-center font-medium">{{ $u->publish_credits }}</td>
                        <td class="px-4 py-3 text-center">{{ $u->ads_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-gray-700">{{ $u->created_at?->format('d/m/Y H:i') }}</div>
                            <div class="text-xs text-gray-400">{{ $u->created_at?->diffForHumans() }}</div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-center text-gray-400" colspan="5">Sin usuarios todavía.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ===== Anuncios ===== --}}
    <section id="ads" class="mb-8 scroll-mt-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Anuncios recientes</h2>
            <span class="text-sm text-gray-400">Incluye borrados</span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">Categoría</th>
                        <th class="px-4 py-3 text-left font-medium">Descripción</th>
                        <th class="px-4 py-3 text-left font-medium">Dueño</th>
                        <th class="px-4 py-3 text-center font-medium">Estado</th>
                        <th class="px-4 py-3 text-center font-medium">Vistas</th>
                        <th class="px-4 py-3 text-left font-medium">Publicado</th>
                        <th class="px-4 py-3 text-left font-medium">Borrado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($ads as $ad)
                    @php
                        if ($ad->trashed()) {
                            $eb = ['Borrado', 'bg-red-100 text-red-700'];
                        } else {
                            $eb = match ($ad->estado) {
                                'active'   => ['Activo', 'bg-green-100 text-green-700'],
                                'inactive' => ['Inactivo', 'bg-gray-100 text-gray-600'],
                                default    => [ucfirst($ad->estado), 'bg-gray-100 text-gray-600'],
                            };
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400">{{ $ad->id }}</td>
                        <td class="px-4 py-3 capitalize text-gray-600">{{ $ad->categoria }}</td>
                        <td class="px-4 py-3">
                            <div class="line-clamp-2 text-gray-700 max-w-xs">{{ $ad->descripcion }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $ad->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $eb[1] }}">{{ $eb[0] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $ad->vistas }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-gray-700">{{ $ad->created_at?->format('d/m/Y H:i') }}</div>
                            <div class="text-xs text-gray-400">{{ $ad->created_at?->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($ad->deleted_at)
                                <div class="text-red-600">{{ $ad->deleted_at->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-gray-400">{{ $ad->deleted_at->diffForHumans() }}</div>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-center text-gray-400" colspan="8">Sin anuncios todavía.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ===== Transacciones ===== --}}
    <section id="transactions" class="scroll-mt-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold">Transacciones recientes</h2>
            <span class="text-sm text-gray-400">Últimas {{ count($transactions) }} de {{ number_format($stats['transactions']) }}</span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Orden / Cargo</th>
                        <th class="px-4 py-3 text-left font-medium">Cliente</th>
                        <th class="px-4 py-3 text-left font-medium">Método</th>
                        <th class="px-4 py-3 text-right font-medium">Monto</th>
                        <th class="px-4 py-3 text-center font-medium">Estado</th>
                        <th class="px-4 py-3 text-left font-medium">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $t)
                    @php
                        $tb = match ($t->status) {
                            'paid'     => ['Pagado', 'bg-green-100 text-green-700'],
                            'pending'  => ['Pendiente', 'bg-yellow-100 text-yellow-700'],
                            'failed'   => ['Fallido', 'bg-red-100 text-red-700'],
                            'refunded' => ['Devuelto', 'bg-red-100 text-red-700'],
                            default    => [ucfirst($t->status), 'bg-gray-100 text-gray-700'],
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $t->order_number ?? $t->charge_id ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $t->user?->name ?? $t->customer_name ?? '—' }}</td>
                        <td class="px-4 py-3 uppercase text-xs text-gray-600">{{ $t->payment_method }}</td>
                        <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ $t->currency ?? 'PEN' }} {{ number_format($t->amount / 100, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tb[1] }}">{{ $tb[0] }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-gray-700">{{ $t->created_at?->format('d/m/Y H:i') }}</div>
                            <div class="text-xs text-gray-400">{{ $t->created_at?->diffForHumans() }}</div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-center text-gray-400" colspan="6">Sin transacciones todavía.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
