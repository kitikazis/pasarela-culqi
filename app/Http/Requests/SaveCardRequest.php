<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para guardar un cliente + tarjeta (cobros one-click).
 */
class SaveCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'        => ['required', 'string', 'starts_with:tkn_live_,tkn_test_'],
            'email'        => ['required', 'email'],
            'first_name'   => ['required', 'string', 'max:50'],
            'last_name'    => ['required', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:15'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.starts_with' => 'El token debe comenzar con tkn_ (tarjeta).',
        ];
    }
}
