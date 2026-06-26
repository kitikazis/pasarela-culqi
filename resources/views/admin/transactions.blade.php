@extends('admin.layouts.app')

@section('title', 'Transacciones - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold">Transacciones</h2>
        <p class="text-sm text-gray-500">{{ number_format($transactions->total()) }} transacciones en total</p>
    </div>

    @include('admin.partials.transactions-table')

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
@endsection
