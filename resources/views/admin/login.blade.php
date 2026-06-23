@extends('admin.layouts.app')

@section('title', 'Login - Admin')

@section('sidebar')
    {{-- Empty sidebar on login --}}
@endsection

@section('content')
<div class="flex items-center justify-center h-[70vh]">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
        <div class="text-center mb-4">
            <div class="mx-auto w-16 h-16 rounded-full bg-brand flex items-center justify-center text-white font-bold">A</div>
            <h2 class="mt-3 text-2xl font-semibold">Acceso Administrador</h2>
            <p class="text-sm text-gray-500">Ingresa tus credenciales para continuar</p>
        </div>

        @error('usuario')
            <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Usuario</label>
            <input name="usuario" type="text" value="{{ old('usuario') }}" required autofocus
                   class="mt-1 mb-3 w-full rounded border-gray-200 focus:ring-brand focus:border-brand">

            <label class="block text-sm font-medium text-gray-700">Contraseña</label>
            <input name="password" type="password" required
                   class="mt-1 mb-4 w-full rounded border-gray-200 focus:ring-brand focus:border-brand">

            <button type="submit" class="w-full py-2 bg-brand text-white rounded hover:brightness-95">Entrar</button>
        </form>
    </div>
</div>
@endsection
