{{-- Botón de una acción sobre un anuncio. Espera $route, $id, $label, $class y opcional $confirm. --}}
<form method="POST" action="{{ route($route, $id) }}"
      @isset($confirm) onsubmit="return confirm(@js($confirm))" @endisset>
    @csrf
    <button type="submit" class="px-2 py-1 rounded border text-xs font-medium whitespace-nowrap transition {{ $class }}">{{ $label }}</button>
</form>
