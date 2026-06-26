@php
    $link   = 'flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-brand/10 transition';
    $active = 'bg-brand/10 text-brand font-medium';
@endphp
<aside class="w-64 bg-white border-r min-h-screen">
    <nav class="p-4 space-y-1">
        <div class="px-2 py-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Navegación</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ $link }} {{ request()->routeIs('admin.dashboard') ? $active : '' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i> Dashboard
        </a>
        <a href="{{ route('admin.users') }}" class="{{ $link }} {{ request()->routeIs('admin.users') ? $active : '' }}">
            <i data-lucide="users" class="w-5 h-5"></i> Usuarios
        </a>
        <a href="{{ route('admin.ads') }}" class="{{ $link }} {{ request()->routeIs('admin.ads') ? $active : '' }}">
            <i data-lucide="megaphone" class="w-5 h-5"></i> Anuncios
        </a>
        <a href="{{ route('admin.transactions') }}" class="{{ $link }} {{ request()->routeIs('admin.transactions') ? $active : '' }}">
            <i data-lucide="credit-card" class="w-5 h-5"></i> Transacciones
        </a>
    </nav>
</aside>
