{{-- Resultados de usuarios. Se recarga por AJAX al filtrar en vivo. --}}
<p class="text-sm text-gray-500 mb-4">{{ number_format($users->total()) }} resultado(s)</p>

@include('admin.partials.users-table')

<div class="mt-4">
    {{ $users->links() }}
</div>
