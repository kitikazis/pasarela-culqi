<?php

/*
|--------------------------------------------------------------------------
| Configuración de Culqi
|--------------------------------------------------------------------------
| Todas las credenciales se leen EXCLUSIVAMENTE desde el archivo .env.
| Nunca se deben hardcodear llaves en este archivo ni en el código.
|
|  - public_key      → se usa en el FRONTEND (Checkout v4) y para tokens
|  - secret_key      → SOLO backend. Jamás exponer al cliente.
|  - rsa_id / rsa_public_key → SOLO backend. Encriptan el payload (RSA+AES)
|    antes de enviarlo a Culqi. Obligatorio en producción.
*/

return [

    'public_key' => env('CULQI_PUBLIC_KEY'),

    'secret_key' => env('CULQI_SECRET_KEY', env('CULQI_PRIVATE_KEY')),

    'rsa_id' => env('CULQI_RSA_ID'),

    'rsa_public_key' => env('CULQI_RSA_PUBLIC_KEY'),

    'base_url' => env('CULQI_BASE_URL', 'https://api.culqi.com/v2'),

    /*
    | Moneda y país por defecto para órdenes / clientes.
    */
    'default_currency' => 'PEN',
    'country_code'     => 'PE',

];
