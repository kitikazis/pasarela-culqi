{{-- Resultados de transacciones. Se recarga por AJAX al filtrar en vivo. --}}
<p class="text-sm text-gray-500 mb-4">{{ number_format($transactions->total()) }} resultado(s)</p>

@include('admin.partials.transactions-table')

<div class="mt-4">
    {{ $transactions->links() }}
</div>
