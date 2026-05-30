<?php

return [
    'accepted'             => 'El campo :attribute debe ser aceptado.',
    'email'                => 'El campo :attribute debe ser una dirección de correo válida.',
    'in'                   => 'El valor seleccionado para :attribute no es válido.',
    'integer'              => 'El campo :attribute debe ser un número entero.',
    'max'                  => [
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string'  => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min'                  => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric'              => 'El campo :attribute debe ser un número.',
    'regex'                => 'El formato del campo :attribute no es válido.',
    'required'             => 'El campo :attribute es obligatorio.',
    'starts_with'          => 'El campo :attribute debe comenzar con: :values.',
    'string'               => 'El campo :attribute debe ser una cadena de texto.',
    'unique'               => 'El valor del campo :attribute ya está en uso.',
    'nullable'             => '',

    'attributes' => [
        'token_id'     => 'token de pago',
        'amount'       => 'monto',
        'currency'     => 'moneda',
        'email'        => 'correo electrónico',
        'description'  => 'descripción',
        'phone_number' => 'número de teléfono',
        'otp'          => 'código OTP',
    ],
];
