<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token_id'    => ['required', 'string', 'starts_with:tkn_'],
            'amount'      => ['required', 'integer', 'min:100'],
            'currency'    => ['nullable', 'string', 'in:PEN,USD'],
            'email'       => ['required', 'email'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'token_id.starts_with' => 'El token_id debe empezar con "tkn_".',
            'amount.min'           => 'El monto mínimo es 100 céntimos (S/1.00).',
            'currency.in'          => 'La moneda debe ser PEN o USD.',
        ];
    }
}
