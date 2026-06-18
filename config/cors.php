<?php

/*
|--------------------------------------------------------------------------
| CORS — Cross-Origin Resource Sharing
|--------------------------------------------------------------------------
| Controla qué ORÍGENES (otros sitios web) pueden llamar al backend desde el
| navegador. Solo se permite tu propia app.
|
| Los dominios se definen en CORS_ALLOWED_ORIGINS del .env (separados por
| comas). Ej. en producción:
|   CORS_ALLOWED_ORIGINS="https://anuncialo.pe,https://www.anuncialo.pe"
|
| Nota: CORS solo bloquea peticiones CRUZADAS desde un navegador. NO bloquea
| llamadas servidor-a-servidor (curl/Postman) ni el webhook de Culqi (esos no
| envían cabecera Origin). Tu app es del mismo origen, así que sigue funcionando.
*/

return [

    // Endpoints sujetos a la política CORS (los que el front consume por fetch).
    'paths' => [
        'api/*',
        'me',
        'logout',
        'auth/*',
        'anuncios',
        'anuncios/*',
        'mis-anuncios/datos',
        'pago/*',
    ],

    'allowed_methods' => ['*'],

    // Solo estos orígenes pueden hacer peticiones cruzadas. Default: tu dominio.
    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'https://anuncialo.pe'))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Permite enviar cookies de sesión (si algún día sirves el front desde otro subdominio).
    'supports_credentials' => true,

];
