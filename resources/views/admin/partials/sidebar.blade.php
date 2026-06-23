<aside class="w-64 bg-white border-r">
    <nav class="p-4 space-y-2">
        <div class="px-2 py-3 text-sm font-semibold text-gray-500">NAVEGACIÓN</div>
        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-brand/10 {{ request()->routeIs('admin.dashboard') ? 'bg-brand/10' : '' }}">Dashboard</a>
        <a href="#users" class="block px-3 py-2 rounded hover:bg-brand/10">Usuarios</a>
        <a href="#ads" class="block px-3 py-2 rounded hover:bg-brand/10">Anuncios</a>
        <a href="#transactions" class="block px-3 py-2 rounded hover:bg-brand/10">Transacciones</a>
    </nav>
</aside>
