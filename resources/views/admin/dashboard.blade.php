@extends('admin.layouts.app')

@section('title', 'Dashboard - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Usuarios</div>
            <div class="text-2xl font-bold">{{ count($users) }}</div>
        </div>
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Anuncios</div>
            <div class="text-2xl font-bold">{{ count($ads) }}</div>
        </div>
        <div class="bg-white rounded shadow p-4">
            <div class="text-sm text-gray-500">Transacciones</div>
            <div class="text-2xl font-bold">{{ count($transactions) }}</div>
        </div>
    </div>

    <section id="users" class="mb-6">
        <h2 class="text-lg font-semibold mb-3">Usuarios</h2>
        <div class="bg-white rounded shadow overflow-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-sm text-gray-600">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Rol</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr class="border-t">
                        <td class="p-3">{{ $u['id'] }}</td>
                        <td class="p-3">{{ $u['name'] }}</td>
                        <td class="p-3">{{ $u['email'] }}</td>
                        <td class="p-3">{{ $u['role'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section id="ads" class="mb-6">
        <h2 class="text-lg font-semibold mb-3">Anuncios</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($ads as $ad)
            <div class="bg-white rounded shadow p-4">
                <div class="text-sm text-gray-500">#{{ $ad['id'] }}</div>
                <div class="font-semibold mt-2">{{ $ad['title'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Por: {{ $ad['user'] }}</div>
                <div class="mt-3"><span class="px-2 py-1 rounded text-sm bg-gray-100">{{ $ad['status'] }}</span></div>
            </div>
            @endforeach
        </div>
    </section>

    <section id="transactions">
        <h2 class="text-lg font-semibold mb-3">Transacciones</h2>
        <div class="bg-white rounded shadow overflow-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-sm text-gray-600">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Usuario</th>
                        <th class="p-3">Monto</th>
                        <th class="p-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($transactions as $t)
                    <tr class="border-t">
                        <td class="p-3">{{ $t['id'] }}</td>
                        <td class="p-3">{{ $t['user'] }}</td>
                        <td class="p-3">{{ $t['amount'] }}</td>
                        <td class="p-3">{{ $t['status'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
