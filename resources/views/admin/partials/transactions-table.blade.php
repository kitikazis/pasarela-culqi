{{-- Tabla de transacciones. Espera $transactions (colección o paginador). --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-4 py-3 text-left font-medium">Orden / Cargo</th>
                <th class="px-4 py-3 text-left font-medium">Cliente</th>
                <th class="px-4 py-3 text-left font-medium">Método</th>
                <th class="px-4 py-3 text-right font-medium">Monto</th>
                <th class="px-4 py-3 text-center font-medium">Estado</th>
                <th class="px-4 py-3 text-left font-medium">Fecha</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @forelse($transactions as $t)
            @php
                $tb = match ($t->status) {
                    'paid'     => ['Pagado', 'bg-green-100 text-green-700'],
                    'pending'  => ['Pendiente', 'bg-yellow-100 text-yellow-700'],
                    'failed'   => ['Fallido', 'bg-red-100 text-red-700'],
                    'refunded' => ['Devuelto', 'bg-red-100 text-red-700'],
                    default    => [ucfirst($t->status), 'bg-gray-100 text-gray-700'],
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $t->order_number ?? $t->charge_id ?? '—' }}</td>
                <td class="px-4 py-3 whitespace-nowrap">{{ $t->user?->name ?? $t->customer_name ?? '—' }}</td>
                <td class="px-4 py-3 uppercase text-xs text-gray-600">{{ $t->payment_method }}</td>
                <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ $t->currency ?? 'PEN' }} {{ number_format($t->amount / 100, 2) }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tb[1] }}">{{ $tb[0] }}</span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-gray-700">{{ $t->created_at?->format('d/m/Y H:i') }}</div>
                    <div class="text-xs text-gray-400">{{ $t->created_at?->diffForHumans() }}</div>
                </td>
            </tr>
        @empty
            <tr><td class="px-4 py-6 text-center text-gray-400" colspan="6">No hay transacciones para mostrar.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
