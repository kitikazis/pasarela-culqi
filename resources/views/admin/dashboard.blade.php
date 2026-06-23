@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Usuarios</div>
            <div class="text-2xl font-bold">{{ $stats['users'] }}</div>
        </div>
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Anuncios</div>
            <div class="text-2xl font-bold">{{ $stats['ads'] }}</div>
        </div>
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Transacciones</div>
            <div class="text-2xl font-bold">{{ $stats['transactions'] }}</div>
        </div>
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Pagos confirmados</div>
            <div class="text-2xl font-bold">{{ $stats['paid'] }}</div>
        </div>
    </div>

    <section id="users" class="mb-6">
        <h2 class="text-lg font-semibold mb-3">Usuarios recientes</h2>
        <div class="bg-white rounded shadow overflow-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-sm text-gray-600">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Proveedor</th>
                        <th class="p-3">Créditos</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    <tr class="border-t">
                        <td class="p-3">{{ $u->id }}</td>
                        <td class="p-3">{{ $u->name }}</td>
                        <td class="p-3">{{ $u->email }}</td>
                        <td class="p-3 capitalize">{{ $u->provider }}</td>
                        <td class="p-3">{{ $u->publish_credits }}</td>
                    </tr>
                @empty
                    <tr><td class="p-3 text-gray-500" colspan="5">Sin usuarios todavía.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section id="ads" class="mb-6">
        <h2 class="text-lg font-semibold mb-3">Anuncios recientes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($ads as $ad)
            <div class="bg-white rounded shadow p-4">
                <div class="text-sm text-gray-500">#{{ $ad->id }} · {{ ucfirst($ad->category) }}</div>
                <div class="font-semibold mt-2">{{ \Illuminate\Support\Str::limit($ad->description, 70) }}</div>
                <div class="text-sm text-gray-500 mt-1">Por: {{ $ad->user?->name ?? '—' }}</div>
                <div class="mt-3">
                    <span class="px-2 py-1 rounded text-sm {{ $ad->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">
                        {{ $ad->status === 'active' ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-gray-500">Sin anuncios todavía.</div>
            @endforelse
        </div>
    </section>

    <section id="transactions">
        <h2 class="text-lg font-semibold mb-3">Transacciones recientes</h2>
        <div class="bg-white rounded shadow overflow-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-sm text-gray-600">
                    <tr>
                        <th class="p-3">Orden</th>
                        <th class="p-3">Usuario</th>
                        <th class="p-3">Método</th>
                        <th class="p-3">Monto</th>
                        <th class="p-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transactions as $t)
                    @php
                        $badge = match ($t->status) {
                            'paid'                 => 'bg-green-100 text-green-700',
                            'pending'              => 'bg-yellow-100 text-yellow-700',
                            'failed', 'refunded'   => 'bg-red-100 text-red-700',
                            default                => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr class="border-t">
                        <td class="p-3">{{ $t->order_number ?? $t->charge_id ?? '—' }}</td>
                        <td class="p-3">{{ $t->user?->name ?? $t->customer_name ?? '—' }}</td>
                        <td class="p-3 uppercase">{{ $t->payment_method }}</td>
                        <td class="p-3">{{ $t->currency ?? 'PEN' }} {{ number_format($t->amount / 100, 2) }}</td>
                        <td class="p-3"><span class="px-2 py-1 rounded text-sm {{ $badge }}">{{ ucfirst($t->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td class="p-3 text-gray-500" colspan="5">Sin transacciones todavía.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
