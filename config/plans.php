<?php

/*
|--------------------------------------------------------------------------
| Planes de publicación (paquetes de créditos)
|--------------------------------------------------------------------------
| Comprar un plan suma 'credits' al usuario. Cada publicación de anuncio
| gasta 1 crédito. Los créditos no vencen.
|
| Los precios son AUTORITATIVOS: el backend resuelve el monto desde aquí
| usando el id del plan; el frontend solo envía el id (no el monto), así el
| usuario no puede manipular el precio.
|
| amount está en céntimos (2500 = S/ 25.00).
| Ajusta libremente 'credits' y 'amount' a tu criterio.
*/

return [

    'basico' => [
        'name'        => 'Plan Básico',
        'credits'     => 1,
        'amount'      => 100,    // S/ 1.00  (prueba)
        'description' => '1 publicación',
        'features'    => ['1 anuncio publicado', 'Los créditos no vencen'],
    ],

    'plus' => [
        'name'        => 'Plan Plus',
        'credits'     => 10,
        'amount'      => 2500,   // S/ 25.00
        'description' => '10 publicaciones',
        'features'    => ['10 anuncios publicados', 'Ahorras vs. comprar de a uno', 'Los créditos no vencen'],
        'popular'     => true,
    ],

    'premium' => [
        'name'        => 'Plan Premium',
        'credits'     => 30,
        'amount'      => 5000,   // S/ 50.00
        'description' => '30 publicaciones',
        'features'    => ['30 anuncios publicados', 'El mejor precio por anuncio', 'Los créditos no vencen'],
    ],

];
