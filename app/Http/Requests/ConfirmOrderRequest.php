<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para confirmar el estado de una Orden Culqi tras el pago.
 */
class ConfirmOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string', 'starts_with:ord_'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.starts_with' => 'La orden indicada no es válida.',
        ];
    }
}
