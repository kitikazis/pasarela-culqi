{{-- Resultados de anuncios. Se recarga por AJAX al filtrar en vivo. --}}
<p class="text-sm text-gray-500 mb-4">{{ number_format($ads->total()) }} resultado(s)</p>

@include('admin.partials.ads-table')

<div class="mt-4">
    {{ $ads->links() }}
</div>
