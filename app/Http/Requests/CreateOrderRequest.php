<?php

namespace App\Http\Requests;

use App\Rules\OwnedAd;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para crear una Orden Culqi.
 * Las órdenes habilitan métodos como PagoEfectivo y Cuotéalo en el Checkout.
 */
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan'         => ['nullable', 'string', 'in:' . implode(',', array_keys(config('plans')))],
            'amount'       => ['required_without:plan', 'integer', 'min:600'], // Culqi billeteras/órdenes: mínimo S/6.00
            'email'        => ['required', 'email'],
            'first_name'   => ['required', 'string', 'max:50'],
            'last_name'    => ['required', 'string', 'max:50'],
            'phone_number' => ['required', 'string', 'max:15'],
            'description'  => ['nullable', 'string', 'max:250'],
            // Anuncio a destacar (opcional). Si viene, debe pertenecer al usuario.
            'ad_id'        => ['nullable', 'integer', new OwnedAd()],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'El monto mínimo para órdenes/billeteras es 600 céntimos (S/6.00).',
        ];
    }
}
