<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class YapeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Si viene un plan válido, el monto se resuelve en el backend (no se confía del cliente).
            'plan'           => ['nullable', 'string', 'in:' . implode(',', array_keys((array) config('plans')))],
            'phone_number'   => ['required', 'string', 'regex:/^9\d{8}$/'],
            'otp'            => ['required', 'string', 'regex:/^\d{6}$/'],
            'amount'         => ['required_without:plan', 'integer', 'min:100'],
            'email'          => ['required', 'email'],
            'first_name'     => ['nullable', 'string', 'max:100'],
            'last_name'      => ['nullable', 'string', 'max:100'],
            'description'    => ['nullable', 'string', 'max:500'],
            'publicacion_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'El número debe tener 9 dígitos y empezar con 9.',
            'otp.regex'          => 'El OTP debe tener exactamente 6 dígitos.',
            'amount.min'         => 'El monto mínimo es 100 céntimos (S/1.00).',
        ];
    }
}
