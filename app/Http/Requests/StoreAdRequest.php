<?php

namespace App\Http\Requests;

use App\Models\Ad;
use App\Rules\NoProfanity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAdRequest extends FormRequest
{
    /** Solo usuarios autenticados pueden publicar. */
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'category'    => ['required', 'string', 'in:venta,compra,trabajo,busca'],
            'description' => ['required', 'string', 'max:' . Ad::MAX_DESCRIPTION, new NoProfanity()],
            'phone'       => ['required', 'string', 'regex:/^9\d{8}$/'],
            'coverage'    => ['required', 'string', 'in:nacional,departamental,provincial,distrital'],
            'department'  => ['required_unless:coverage,nacional', 'nullable', 'string', 'max:60'],
            'province'    => ['required_if:coverage,provincial,distrital', 'nullable', 'string', 'max:60'],
            'district'    => ['required_if:coverage,distrital', 'nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.in'         => 'La categoría no es válida.',
            'description.max'     => 'La descripción no puede superar ' . Ad::MAX_DESCRIPTION . ' caracteres.',
            'phone.regex'         => 'El celular debe tener 9 dígitos y empezar con 9.',
            'department.required_unless' => 'Selecciona el departamento.',
            'province.required_if'       => 'Selecciona la provincia.',
            'district.required_if'       => 'Selecciona el distrito.',
        ];
    }
}
