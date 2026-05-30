<?php

/*
|--------------------------------------------------------------------------
| Planes de pago
|--------------------------------------------------------------------------
| Los precios son AUTORITATIVOS: el backend siempre resuelve el monto a
| cobrar desde aquí usando el id del plan. El frontend nunca envía el monto,
| solo el id del plan — así un usuario no puede manipular el precio.
|
| amount está en céntimos (1000 = S/ 10.00).
*/

return [

    'basico' => [
        'name'        => 'Plan Básico',
        'description' => 'Destaca tu anuncio durante 7 días',
        'days'        => 7,
        'amount'      => 600,    // S/ 6.00  ← mínimo Culqi para billeteras. Producción: 1000
        'features'    => ['Aparece primero en tu categoría', 'Insignia "Destacado"'],
    ],

    'plus' => [
        'name'        => 'Plan Plus',
        'description' => 'Destaca tu anuncio durante 30 días',
        'days'        => 30,
        'amount'      => 2500,   // S/ 25.00
        'features'    => ['Todo lo del Básico', 'Prioridad en búsquedas', 'Estadísticas de visitas'],
        'popular'     => true,
    ],

    'premium' => [
        'name'        => 'Plan Premium',
        'description' => 'Destaca tu anuncio durante 90 días',
        'days'        => 90,
        'amount'      => 5000,   // S/ 50.00
        'features'    => ['Todo lo del Plus', 'Posición #1 garantizada', 'Renovación automática'],
    ],

];
