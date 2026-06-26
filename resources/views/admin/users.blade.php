@extends('admin.layouts.app')

@section('title', 'Usuarios - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold">Usuarios</h2>
        <p class="text-sm text-gray-500">{{ number_format($users->total()) }} usuarios registrados</p>
    </div>

    @include('admin.partials.users-table')

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
