@extends('admin.layouts.app')

@section('title', 'Anuncios - Admin')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-bold">Anuncios</h2>
        <p class="text-sm text-gray-500">{{ number_format($ads->total()) }} anuncios en total (incluye borrados)</p>
    </div>

    @include('admin.partials.ads-table')

    <div class="mt-4">
        {{ $ads->links() }}
    </div>
@endsection
