<?php

namespace App\Rules;

use App\Models\Ad;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Valida que el anuncio (ad_id) exista y PERTENEZCA al usuario autenticado.
 * Evita que alguien destaque/pague por un anuncio ajeno. Si no hay sesión,
 * Auth::id() es null y la comprobación falla (no se puede destacar como invitado).
 */
class OwnedAd implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isOwner = Ad::where('id', $value)
            ->where('user_id', Auth::id())
            ->exists();

        if (! $isOwner) {
            $fail('El anuncio indicado no existe o no te pertenece.');
        }
    }
}
