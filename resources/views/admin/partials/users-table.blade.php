{{-- Tabla de usuarios. Espera $users (colección o paginador). --}}
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
                <td class="px-4 py-3 text-center">{{ $u->ads_count ?? '—' }}</td>
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
