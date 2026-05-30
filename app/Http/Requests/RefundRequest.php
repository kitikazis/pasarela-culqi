<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        // En producción, validar aquí que el usuario tenga permiso de devolución.
        return true;
    }

    public function rules(): array
    {
        return [
            'charge_id' => ['required', 'string', 'starts_with:chr_live_,chr_test_'],
            'amount'    => ['required', 'integer', 'min:100'],
            // Razones válidas según Culqi API v2.0
            'reason'    => ['required', 'string', 'in:duplicado,fraudulento,solicitud_comprador'],
        ];
    }

    public function messages(): array
    {
        return [
            'charge_id.starts_with' => 'El charge_id debe comenzar con chr_live_ o chr_test_.',
            'amount.min'            => 'El monto mínimo de devolución es 100 céntimos (S/1.00).',
            'reason.in'             => 'La razón debe ser: duplicado, fraudulento o solicitud_comprador.',
        ];
    }
}
