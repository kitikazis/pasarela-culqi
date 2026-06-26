{{-- Tabla de anuncios (incluye borrados). Espera $ads (colección o paginador). --}}
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
            <tr><td class="px-4 py-6 text-center text-gray-400" colspan="8">No hay anuncios para mostrar.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
