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
            'token'         => ['required', 'string', 'starts_with:tkn_live_,tkn_test_'],
            // Se cobra por PLAN (precio resuelto en el backend) o por monto directo.
            'plan'          => ['nullable', 'string', 'in:' . implode(',', array_keys(config('plans')))],
            'amount'        => ['required_without:plan', 'integer', 'min:100'],
            'currency_code' => ['nullable', 'string', 'in:PEN,USD'],
            'email'         => ['required', 'email'],
            'description'   => ['nullable', 'string', 'max:250'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'        => 'El token de pago es obligatorio.',
            'token.starts_with'     => 'El token debe comenzar con tkn_live_ o tkn_test_.',
            'plan.in'               => 'El plan seleccionado no es válido.',
            'amount.required_without' => 'Debes indicar un plan o un monto.',
            'amount.min'            => 'El monto mínimo es 100 céntimos (S/1.00).',
            'currency_code.in'      => 'La moneda debe ser PEN o USD.',
            'description.max'       => 'La descripción no puede superar 250 caracteres.',
        ];
    }
}
