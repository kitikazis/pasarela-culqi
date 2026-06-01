<?php

namespace App\Rules;

use App\Services\ContentModerator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rechaza contenido no permitido (groserías o servicios para adultos).
 * Delega en ContentModerator (lista local + IA opcional).
 */
class NoProfanity implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $reason = app(ContentModerator::class)->check((string) $value);

        if ($reason !== null) {
            $fail($reason);
        }
    }
}
